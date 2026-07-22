package main

import (
	"crypto"
	"crypto/rsa"
	"crypto/sha256"
	"crypto/x509"
	"encoding/base64"
	"encoding/json"
	"encoding/pem"
	"fmt"
	"net/url"
	"os"
	"sort"
	"strings"
)

func verifyEasylinkWebhookSignature(appKey, nonce, timestamp string, body map[string]interface{}, signatureBase64, easylinkPublicKeyPem string) bool {
	params := make(map[string]string)
	params["X-EasyLink-AppKey"] = appKey
	params["X-EasyLink-Nonce"] = nonce
	params["X-EasyLink-Timestamp"] = timestamp

	for k, v := range body {
		switch val := v.(type) {
		case map[string]interface{}, []interface{}:
			jsonBytes, _ := json.Marshal(val)
			params[k] = string(jsonBytes)
		default:
			params[k] = fmt.Sprintf("%v", val)
		}
	}

	var keys []string
	for k := range params {
		keys = append(keys, k)
	}
	sort.Strings(keys)

	var pairs []string
	for _, k := range keys {
		pairs = append(pairs, fmt.Sprintf("%s=%s", k, url.QueryEscape(params[k])))
	}
	originalString := strings.Join(pairs, "&")
	stringToSign := appKey + originalString + appKey

	pemData := []byte(easylinkPublicKeyPem)
	if !strings.Contains(easylinkPublicKeyPem, "-----BEGIN") {
		data, err := os.ReadFile(easylinkPublicKeyPem)
		if err != nil {
			return false
		}
		pemData = data
	}

	block, _ := pem.Decode(pemData)
	if block == nil {
		return false
	}

	pubKey, err := x509.ParsePKIXPublicKey(block.Bytes)
	if err != nil {
		return false
	}

	rsaPubKey, ok := pubKey.(*rsa.PublicKey)
	if !ok {
		return false
	}

	rawSignature, err := base64.StdEncoding.DecodeString(signatureBase64)
	if err != nil {
		return false
	}

	hashed := sha256.Sum256([]byte(stringToSign))
	err = rsa.VerifyPKCS1v15(rsaPubKey, crypto.SHA256, hashed[:], rawSignature)

	return err == nil
}

func main() {
	appKey := "YOUR_APP_KEY"
	easylinkPublicKeyPem := "/path/to/easylink_public_key.pem"

	nonce := "dummy_nonce"
	timestamp := "1700000000000"
	signatureBase64 := "DUMMY_SIGNATURE"
	body := map[string]interface{}{"referenceNo": "REF-12345", "status": "SUCCESS"}

	fmt.Println("Verifying Webhook Signature...")
	isValid := verifyEasylinkWebhookSignature(appKey, nonce, timestamp, body, signatureBase64, easylinkPublicKeyPem)

	if isValid {
		fmt.Println("Webhook signature is VALID!")
	} else {
		fmt.Println("Invalid Webhook signature!")
	}
}
