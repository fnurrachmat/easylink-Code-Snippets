package main

import (
	"bytes"
	"crypto"
	"crypto/rand"
	"crypto/rsa"
	"crypto/sha256"
	"crypto/x509"
	"encoding/base64"
	"encoding/hex"
	"encoding/json"
	"encoding/pem"
	"fmt"
	"io"
	"net/http"
	"net/url"
	"os"
	"sort"
	"strings"
	"time"
)

// PhpUrlEncode mimics PHP's urlencode (spaces encoded as '+').
func PhpUrlEncode(str string) string {
	return url.QueryEscape(str)
}

// GenerateEasylinkSignature generates RSA-SHA256 signature for Easylink API requests.
func GenerateEasylinkSignature(appKey, nonce, timestamp string, body map[string]interface{}, privateKeyPem string) (string, error) {
	params := make(map[string]string)
	params["X-EasyLink-AppKey"] = appKey
	params["X-EasyLink-Nonce"] = nonce
	params["X-EasyLink-Timestamp"] = timestamp

	for k, v := range body {
		switch val := v.(type) {
		case map[string]interface{}:
			for k2, v2 := range val {
				params[fmt.Sprintf("%s.%s", k, k2)] = fmt.Sprintf("%v", v2)
			}
		default:
			params[k] = fmt.Sprintf("%v", val)
		}
	}

	// Sort keys alphabetically
	var keys []string
	for k := range params {
		keys = append(keys, k)
	}
	sort.Strings(keys)

	// Build key=value string
	var pairs []string
	for _, k := range keys {
		pairs = append(pairs, fmt.Sprintf("%s=%s", k, PhpUrlEncode(params[k])))
	}
	originalString := strings.Join(pairs, "&")
	stringToSign := appKey + originalString + appKey

	pemData := []byte(privateKeyPem)
	if !strings.Contains(privateKeyPem, "-----BEGIN") {
		data, err := os.ReadFile(privateKeyPem)
		if err != nil {
			return "", fmt.Errorf("read private key file error: %w", err)
		}
		pemData = data
	}

	block, _ := pem.Decode(pemData)
	if block == nil {
		return "", fmt.Errorf("failed to decode PEM block")
	}

	privateKey, err := x509.ParsePKCS8PrivateKey(block.Bytes)
	if err != nil {
		privateKey, err = x509.ParsePKCS1PrivateKey(block.Bytes)
		if err != nil {
			return "", fmt.Errorf("parse private key error: %w", err)
		}
	}

	rsaKey, ok := privateKey.(*rsa.PrivateKey)
	if !ok {
		return "", fmt.Errorf("not an RSA private key")
	}

	hashed := sha256.Sum256([]byte(stringToSign))
	signature, err := rsa.SignPKCS1v15(rand.Reader, rsaKey, crypto.SHA256, hashed[:])
	if err != nil {
		return "", fmt.Errorf("sign error: %w", err)
	}

	return base64.StdEncoding.EncodeToString(signature), nil
}

// SendEasylinkRequest executes an HTTP request to Easylink API.
func SendEasylinkRequest(baseURL, endpoint, method string, payload map[string]interface{}, appKey, privateKeyPem, accessToken string) (int, map[string]interface{}, error) {
	fullURL := fmt.Sprintf("%s/%s", strings.TrimRight(baseURL, "/"), strings.TrimLeft(endpoint, "/"))
	timestamp := fmt.Sprintf("%d", time.Now().UnixNano()/int64(time.Millisecond))
	
	nonceBytes := make([]byte, 16)
	rand.Read(nonceBytes)
	nonce := hex.EncodeToString(nonceBytes)

	var reqBody io.Reader
	if len(payload) > 0 && strings.ToUpper(method) != "GET" {
		jsonBytes, _ := json.Marshal(payload)
		reqBody = bytes.NewBuffer(jsonBytes)
	}

	req, err := http.NewRequest(strings.ToUpper(method), fullURL, reqBody)
	if err != nil {
		return 0, nil, err
	}

	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("Accept", "application/json")

	if accessToken != "" {
		req.Header.Set("Authorization", "Bearer "+accessToken)
		sig, err := GenerateEasylinkSignature(appKey, nonce, timestamp, payload, privateKeyPem)
		if err != nil {
			return 0, nil, err
		}
		req.Header.Set("X-EasyLink-AppKey", appKey)
		req.Header.Set("X-EasyLink-Nonce", nonce)
		req.Header.Set("X-EasyLink-Timestamp", timestamp)
		req.Header.Set("X-Signature", sig)
		req.Header.Set("X-EasyLink-Sign", sig)
	}

	client := &http.Client{Timeout: 30 * time.Second}
	resp, err := client.Do(req)
	if err != nil {
		return 0, nil, err
	}
	defer resp.Body.Close()

	respBytes, _ := io.ReadAll(resp.Body)
	var resultMap map[string]interface{}
	json.Unmarshal(respBytes, &resultMap)

	return resp.StatusCode, resultMap, nil
}
