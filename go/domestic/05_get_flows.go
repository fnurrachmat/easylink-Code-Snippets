package main

import (
	"encoding/json"
	"fmt"
	"time"
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

	now := time.Now()
	sevenDaysAgo := now.AddDate(0, 0, -7)

	payload := map[string]interface{}{
		"start_time": sevenDaysAgo.Format("2006-01-02 15:04:05"),
		"end_time":   now.Format("2006-01-02 15:04:05"),
	}

	fmt.Println("Fetching Cash Flows...")
	_, res, err := SendEasylinkRequest(baseURL, "/v2/transfer/get-flow", "POST", payload, appKey, privateKeyPem, accessToken)
	if err != nil {
		fmt.Printf("Error: %v\n", err)
	} else {
		resBytes, _ := json.MarshalIndent(res, "", "  ")
		fmt.Printf("Response:\n%s\n", string(resBytes))
	}
}
