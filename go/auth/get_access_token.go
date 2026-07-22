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

func main() {
	payload := map[string]interface{}{
		"appId":     appID,
		"appSecret": appSecret,
	}

	fmt.Println("Requesting Access Token...")
	statusCode, res, err := SendEasylinkRequest(baseURL, "/get-access-token", "POST", payload, "", "", "")
	if err != nil {
		log.Fatalf("Error: %v", err)
	}
	if statusCode != 200 {
		log.Fatalf("Failed with status %d: %v", statusCode, res)
	}

	fmt.Printf("Access Token Response:\n%v\n", res)
}
