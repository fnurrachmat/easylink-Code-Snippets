package main

import (
	"encoding/json"
	"fmt"
)

func main() {
	baseURL := "https://sandbox.easylink.id"
	appID := "YOUR_APP_ID"
	appSecret := "YOUR_APP_SECRET"
	appKey := "YOUR_APP_KEY"
	privateKeyPem := "/path/to/private_key.pem"

	accessToken, err := GetAccessToken(baseURL, appID, appSecret)
	if err != nil {
		fmt.Printf("Get token error: %v\n", err)
		return
	}

	payload := map[string]interface{}{
		"page_size":   10,
		"page_number": 1,
	}

	fmt.Println("Fetching Remittance List...")
	_, res, err := SendEasylinkRequest(baseURL, "/transfer/get-remittance-list", "POST", payload, appKey, privateKeyPem, accessToken)
	if err != nil {
		fmt.Printf("Error: %v\n", err)
	} else {
		resBytes, _ := json.MarshalIndent(res, "", "  ")
		fmt.Printf("Response:\n%s\n", string(resBytes))
	}
}
