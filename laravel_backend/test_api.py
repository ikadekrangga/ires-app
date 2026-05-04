import requests
import json

# URL API Laravel kamu
# Pastikan port-nya sesuai (biasanya 8000)
url = "http://0.0.0.0:9000/api/insights/store"

# Data pura-pura (Dummy) yang mau dikirim
payload = {
    # ID ini HARUS sama dengan yang ada di database (yang kita buat di Tinker tadi)
    "instagram_business_id": "IG_TEST_001", 
    
    # Data metrics sesuai struktur controller kamu
    "metrics": {
        "reach": 1500,
        "impressions": 3500,
        "profile_views": 45,  # Ingat typo 'profile_vies' sudah kita perbaiki jadi 'views'
    }
}

headers = {
    "Content-Type": "application/json",
    "Accept": "application/json"
}

print(f"📡 Mengirim data ke: {URL}...")

try:
    # Kirim request POST
    response = requests.post(URL, json=payload, headers=headers)

    # Cek status code
    if response.status_code == 200:
        print("✅ SUKSES! Data berhasil diterima Laravel.")
        print("Respon Server:", response.json())
    elif response.status_code == 404:
        print("❌ GAGAL: Akun tidak ditemukan.")
        print("Pastikan 'ig_page_id' di payload sama dengan di database.")
    else:
        print(f"⚠️ ERROR: Status Code {response.status_code}")
        print("Respon:", response.text)

except requests.exceptions.ConnectionError:
    print("⛔ KONEKSI DITOLAK: Pastikan server Laravel sudah jalan ('php artisan serve').")