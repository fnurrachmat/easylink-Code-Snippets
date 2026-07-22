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
		"partnerReferenceNo": fmt.Sprintf("INT-%d", time.Now().Unix()),
		"senderCountry":      "ID",
		"receiverCountry":    "SG",
		"sourceCurrency":     "IDR",
		"targetCurrency":     "SGD",
		"amount":             1000000,
		"recipientName":      "Alice Smith",
		"accountNumber":      "9876543210",
		"bankName":           "DBS Bank",
		"swiftCode":          "DBSSSGSG",
	}

	fmt.Println("Creating International Transfer...")
	_, res, err := SendEasylinkRequest(baseURL, "/transfer/create-international-transfer", "POST", payload, appKey, privateKeyPem, accessToken)
	if err != nil {
		fmt.Printf("Error: %v\n", err)
	} else {
		resBytes, _ := json.MarshalIndent(res, "", "  ")
		fmt.Printf("Response:\n%s\n", string(resBytes))
	}
}
