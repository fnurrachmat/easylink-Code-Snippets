package main

import (
	"encoding/json"
	"fmt"
	"time"
)

func main() {
	baseURL := "https://sandbox.easylink.id"
	appID := "lQNJ0nL07Ucmemaa"
	appSecret := "HrfFeuRmoyBsZhxDi3w3JNdxwYu19lL4"
	appKey := "3f9a7f74-de23-4fde-af75-da7684528a59"
	privateKeyPem := "../../private_key.pem"

	accessToken, err := GetAccessToken(baseURL, appID, appSecret)
	if err != nil {
		fmt.Printf("Get token error: %v\n", err)
		return
	}

	payload := map[string]interface{}{
		"reference":           fmt.Sprintf("REF-%d", time.Now().UnixNano()),
		"amount":              100000,
		"bank_id":             "1",
		"account_number":      "1234567890",
		"account_holder_name": "John Doe",
		"remark":              "Payment for Order #1001",
	}

	fmt.Println("Creating Domestic Transfer...")
	_, res, err := SendEasylinkRequest(baseURL, "/v2/transfer/create-domestic-transfer", "POST", payload, appKey, privateKeyPem, accessToken)
	if err != nil {
		fmt.Printf("Error: %v\n", err)
	} else {
		resBytes, _ := json.MarshalIndent(res, "", "  ")
		fmt.Printf("Response:\n%s\n", string(resBytes))
	}
}
