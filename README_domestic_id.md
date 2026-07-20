# Dokumentasi Payout Domestic (Transfer Domestik & E-Wallet) - Bahasa Indonesia

Modul `PayoutDomestic` digunakan untuk menangani transfer uang domestik (lokal) di Indonesia, baik transfer antar bank maupun transfer ke saldo e-wallet.

## Inisialisasi Modul
Langkah pertama, inisialisasi modul payout domestic dengan melewatkan instance `Client` yang telah dikonfigurasi:

```php
use EasylinkIntegrator\Modules\PayoutDomestic;

$payoutDomestic = new PayoutDomestic($client);
```

---

## 1. Cek Saldo Merchant (Cek Balances)
Metode ini digunakan untuk mengetahui saldo akun merchant Anda di Easylink.

```php
try {
    $balances = $payoutDomestic->getBalances();
    // Atau bisa menggunakan alias: $balances = $payoutDomestic->listAllBalances();
    
    print_r($balances);
} catch (\EasylinkIntegrator\Exceptions\EasylinkException $e) {
    echo "Gagal mengambil saldo: " . $e->getMessage();
}
```

---

## 2. Dapatkan Daftar Bank & E-Wallet yang Didukung
Gunakan metode ini untuk mengambil ID/kode bank atau e-wallet yang dapat digunakan sebagai target transfer.

```php
// Daftar Bank Lokal
$banks = $payoutDomestic->getSupportedBanks();
print_r($banks);

// Daftar E-Wallet (OVO, DANA, GOPAY, ShopeePay, dll)
$ewallets = $payoutDomestic->getSupportedEwallets();
print_r($ewallets);
```

---

## 3. Verifikasi Rekening Bank (Inquiry / Cek Nama)
Sangat direkomendasikan melakukan verifikasi nama pemilik rekening sebelum melakukan transfer untuk meminimalkan salah transfer.

```php
try {
    $response = $payoutDomestic->verifyBankAccount([
        'account_number' => '1234567890',
        'bank_id'        => '2', // ID bank dari getSupportedBanks()
        'payment_method' => '1', // 1 = Bank Transfer, 2 = E-Wallet
    ]);
    
    if ($response['code'] === 0) {
        echo "Nama Pemilik: " . $response['data']['account_name'];
    }
} catch (\EasylinkIntegrator\Exceptions\EasylinkException $e) {
    echo "Gagal verifikasi rekening: " . $e->getMessage();
}
```

---

## 4. Buat Transfer Domestik (Domestic Payout)
Metode ini digunakan untuk mengirim dana ke rekening bank domestik atau e-wallet.

```php
try {
    $transfer = $payoutDomestic->createTransfer([
        'reference'           => 'UNIQUE_TX_ID_' . time(),
        'bank_id'             => '1', // ID bank/e-wallet target
        'account_holder_name' => 'Nama Penerima',
        'account_number'      => '888801000157508',
        'amount'              => '50000', // Jumlah transfer dalam IDR
        'payment_method'      => 1, // 1 = Bank, 2 = E-wallet
        'description'         => 'Pembayaran Invoice #102',
    ]);
    
    if ($transfer['code'] === 0) {
        echo "Transfer berhasil diproses. ID Transaksi: " . $transfer['data']['disbursement_id'];
    }
} catch (\EasylinkIntegrator\Exceptions\EasylinkException $e) {
    echo "Transfer gagal: " . $e->getMessage();
    print_r($e->getResponsePayload());
}
```

---

## 5. Cek Status Transaksi Domestik
Gunakan reference ID transaksi Anda untuk memantau status transfer yang sedang diproses.

```php
try {
    $status = $payoutDomestic->getDomesticTransfer([
        'reference' => 'YOUR_TRANSACTION_REFERENCE_ID'
    ]);
    print_r($status);
} catch (\EasylinkIntegrator\Exceptions\EasylinkException $e) {
    echo "Gagal memantau transaksi: " . $e->getMessage();
}
```

---

## 6. Riwayat Transaksi Remitansi (Remittance List)
Metode ini mengembalikan daftar transaksi remitansi yang pernah dilakukan dalam jangka waktu tertentu.

```php
try {
    $remittanceList = $payoutDomestic->getRemittanceList([
        'start_datetime' => '2025-03-01T00:00:00.000Z',
        'end_datetime'   => '2026-03-31T00:00:00.000Z',
        'page_size'      => '5',
        'page_number'    => '1',
    ]);
    print_r($remittanceList);
} catch (\EasylinkIntegrator\Exceptions\EasylinkException $e) {
    echo "Gagal mengambil riwayat transaksi: " . $e->getMessage();
}
```

---

## 7. Mutasi Saldo & Aliran Dana (Flow List)
Mendapatkan riwayat mutasi dana/aliran rekening (debit/kredit) akun merchant.

```php
try {
    $flows = $payoutDomestic->getFlows([
        'start_time' => '2025-12-01 21:00:00',
        'end_time'   => '2025-12-30 01:00:00',
        'last_id'    => 9,
        'count'      => 5,
    ]);
    print_r($flows);
} catch (\EasylinkIntegrator\Exceptions\EasylinkException $e) {
    echo "Gagal mengambil mutasi saldo: " . $e->getMessage();
}
```
