package main

import (
	"encoding/json"
	"fmt"
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
		"reference": "INT-1784708959",
	}

	fmt.Println("Confirming International Transfer...")
	_, res, err := SendEasylinkRequest(baseURL, "/transfer/confirm-international-transfer", "POST", payload, appKey, privateKeyPem, accessToken)
	if err != nil {
		fmt.Printf("Error: %v\n", err)
	} else {
		resBytes, _ := json.MarshalIndent(res, "", "  ")
		fmt.Printf("Response:\n%s\n", string(resBytes))
	}
}
