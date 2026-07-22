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

	payload := map[string]interface{}{
		"reference": fmt.Sprintf("INT-%d", time.Now().UnixNano()),
		"source": map[string]interface{}{
			"country":                     "IDN",
			"currency":                    "IDR",
			"segment":                     "business",
			"company_name":                "PT Merchant Indonesia",
			"company_trading_name":        "Merchant ID",
			"company_registration_number": "123456789",
			"company_registration_country": "IDN",
			"address_line":                "Jl. Sudirman No. 1",
			"address_city":                "Jakarta",
			"address_country":             "IDN",
		},
		"destination": map[string]interface{}{
			"country":                  "SGP",
			"currency":                 "SGD",
			"segment":                  "business",
			"beneficiary_account_type": "Bank Account",
			"company_name":             "DBS Bank Corporate",
			"swift_code":               "DBSSSGSG",
			"bank_account_number":      "9876543210",
			"address_line":             "12 Marina Boulevard",
			"address_city":             "Singapore",
			"address_country":          "SGP",
			"source_of_income_code":    "01",
			"purpose_code":             "01",
			"relation_code":            "04",
		},
		"transaction": map[string]interface{}{
			"amount":               1000000,
			"destination_amount":   35,
			"destination_country":  "SGP",
			"destination_currency": "SGD",
		},
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
