package main

import (
	"fmt"
	"log"
)

const (
	baseURL   = "https://sandbox.easylink.id"
	appID     = "YOUR_APP_ID"
	appSecret = "YOUR_APP_SECRET"
)

func GetAccessToken(baseURL, appID, appSecret string) (string, error) {
	payload := map[string]interface{}{
		"app_id":     appID,
		"app_secret": appSecret,
	}

	statusCode, res, err := SendEasylinkRequest(baseURL, "/get-access-token", "POST", payload, "", "", "")
	if err != nil {
		return "", err
	}
	if statusCode != 200 {
		return "", fmt.Errorf("request failed with status %d: %v", statusCode, res)
	}

	if token, ok := res["data"].(string); ok {
		return token, nil
	}
	if token, ok := res["accessToken"].(string); ok {
		return token, nil
	}
	if token, ok := res["access_token"].(string); ok {
		return token, nil
	}

	return "", fmt.Errorf("access token not found in response: %v", res)
}

func main() {
	fmt.Println("Requesting Access Token...")
	token, err := GetAccessToken(baseURL, appID, appSecret)
	if err != nil {
		log.Fatalf("Error: %v", err)
	}
	fmt.Printf("Access Token retrieved successfully:\n%s\n", token)
}
