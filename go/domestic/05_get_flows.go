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
