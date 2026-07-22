package main

import (
	"encoding/json"
	"fmt"
)

func main() {
	baseURL := "https://sandbox.easylink.id"
	appKey := "YOUR_APP_KEY"
	privateKeyPem := "/path/to/private.pem"
	accessToken := "YOUR_ACCESS_TOKEN"

	payload := map[string]interface{}{
		"bankCode":      "BCA",
		"accountNumber": "1234567890",
	}

	fmt.Println("Verifying Bank Account...")
	_, res, err := SendEasylinkRequest(baseURL, "/v2/transfer/verify-bank-account", "POST", payload, appKey, privateKeyPem, accessToken)
	if err != nil {
		fmt.Printf("Error: %v\n", err)
	} else {
		resBytes, _ := json.MarshalIndent(res, "", "  ")
		fmt.Printf("Response:\n%s\n", string(resBytes))
	}
}
