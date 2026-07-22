package main

import (
	"encoding/json"
	"fmt"
	"time"
)

func main() {
	baseURL := "https://sandbox.easylink.id"
	appKey := "YOUR_APP_KEY"
	privateKeyPem := "/path/to/private.pem"
	accessToken := "YOUR_ACCESS_TOKEN"

	payload := map[string]interface{}{
		"partnerReferenceNo": fmt.Sprintf("REF-%d", time.Now().Unix()),
		"amount":             100000,
		"bankCode":           "BCA",
		"accountNumber":      "1234567890",
		"recipientName":      "John Doe",
		"remark":             "Payment for Order #1001",
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
