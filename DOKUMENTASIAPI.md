## 📦 API Endpoint Pelanggan


## Base Url : http://localhost:8000/api

##  1. **Register/login Pelanggan**
### 🔍 POST `/register`
```
Content-Type: application/json
Accept: application/json
```
Untuk Register Pelanggan.

**Request body:**
```json
{
  "nama_pelanggan": "zhenya",
  "email": "krumpyayee@gmail.com",
  "password": "Krumpy180907@",
  "no_hp_pelanggan": "081234567890"
}
```

**Response sukses:**

```json
{
    "message": "Registrasi berhasil! Silakan cek email untuk kode verifikasi.",
    "status": "sukses",
    "registerResult": {
        "email": "krumpayee@gmail.com",
        "namaPelanggan": "zhenya",
        "token_verifikasi": "token",
        "otp": string
    }
}
```

**Response gagal: 422**
Respon 1:

```json
{
    "message": "Email sudah dipakai",
    "errors": {
        "email": [
            "Email sudah dipakai"
        ]
    }
}
```

Respon 2:
```json
{
    "message": "Password harus mengandung huruf besar, huruf kecil, angka, dan simbol",
    "errors": {
        "password": [
            "Password harus mengandung huruf besar, huruf kecil, angka, dan simbol"
        ]
    }
}
```

### 🔍 POST `/verify-email`
```
Verifikasi Email
```

**Header:**
```
Authorization: Bearer {token} (pake token_verifikasi)
Content-Type: application/json
```
Verifikasi email pelanggan.

**Request body:**
```json
{
  "otp": "511383"
}
```

**Response sukses:**

```json
{
    "message": "Verifikasi email berhasil!",
    "status": "sukses"
}
```

**Response gagal 422:**

```json
{
    "message": "Token verifikasi tidak valid.",
    "status": "error"
}
```

### 🔍 POST `/resend-otp`
```
Resend OTP pake token_verifikasi
```

**Header:**
```
Authorization: Bearer {token} (pake token_verifikasi)
Content-Type: application/json
```
Resend OTP kalo ga kekirim.

**Response sukses:**

```json
{
    "message": "Kode OTP berhasil dikirim ulang.",
    "status": "sukses",
    "resendResult": {
        "email": "krumpyayee@gmail.com",
        "otp": 111111,
        "token_verifikasi": "token"
    }
}
```

**Response gagal 400:**

```json
{
    "message": "Token verifikasi tidak ditemukan.",
    "status": "error"
}
```

### 📤 POST `/login`

Login pelanggan.

**Header:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "email": "krumpyayee@gmail.com",
  "password": "Password1234@"
}
```


**Response sukses:**

```json
{
    "error": false,
    "message": "success",
    "loginResult": {
        "email": "krumpyayee@gmail.com",
        "namaPelanggan": "zhenya",
        "token": "token"
    }
}
```

**Response gagal 401:**

```json
{
    "error": true,
    "message": "Email atau password salah"
}
```


##  2. Reset Password
###  POST `/send-reset-email`
```
🔍 Request otp reset password
```
**Header:**
```
Content-Type: application/json
```
**Request Body:**
```json
{
  "email": "krumpyayee@gmail.com"
}

```

**Response sukses:**
```json
{
    "message": "Kode OTP sudah dikirim ke email.",
    "status": "success",
    "otp": 591909
}
```

**Response error 422:**
```json
{
    "message": "Email wajib diisi.",
    "errors": {
        "email": [
            "Email wajib diisi."
        ]
    }
}
```

###  POST `/verify-reset-otp`
```
🔍 Masukin otp dari email
```

**Header:**
```
Content-Type: application/json
```

**Request body:**
```json
{
  "email": "grumpyayeee@gmail.com",
  "otp": "59190" 
}
```

**Response sukses:**
```json
{
    "error": false,
    "message": "success",
    "resetResult": {
        "email": "krumpyayee@gmail.com",
        "token_reset": "token"
    }
}
```

**Response error 422:**
```json
{
    "error": true,
    "message": "OTP salah."
}
```


###  POST `/reset-password`
```
🔍 reset password
```
**Header:**
```
Content-Type: application/json
Accept: application/son
```

```json
{
  "password": "Test@123easy!",
  "password_confirmation": "Test@123easy!"
}

```

**Response sukses:**
```json
{
    "message": "Password berhasil diubah.",
    "status": "success"
}
```

**Response error 422:**
```json
{
    "message": "Konfirmasi password tidak cocok",
    "errors": {
        "password": [
            "Konfirmasi password tidak cocok"
        ]
    }
}
```


##  3. **Profil pelanggan**
### 🛍  GET `/profil`
**Header:**
```
Authorization: Bearer {token} 
```
Ambil data pelanggan dari db.


**Response sukses:**

```json
{
    "message": {
        "namaPelanggan": "Zhenya",
        "email": "krumpyayee@gmail.com",
        "noHpPelanggan": "081234567890",
        "fotoProfil": "linkfoto"
    },
    "status": "sukses"
}
```

**Response error 401:**

```json
{
    "message": "Token tidak ditemukan"
}
```

##  4. **Membership**
### 🛍 POST `/membership`
**Header:**
```
Authorization: Bearer {token} 
```
Beli membership.

**Request Body:**
```json
{
  "bank_pengirim": "BCA"
}
```

**Response sukses:**

```json
{
    "error": false,
    "message": "Pembelian membership berhasil dicatat. Silakan transfer ke rekening berikut.",
    "data": {
        "idMembership": 17,
        "tanggalPembelian": "2025-06-22 16:13:04",
        "namaBankTujuan": "BSI",
        "noRekeningTujuan": "7123456789",
        "atasNama": "PT KIDSNESIA EDUPARK KREASI",
        "jumlahTransfer": 50000,
        "statusPembayaranMembership": "Pending"
    }
}
```
**Response error 401:**

```json
{
    "message": "Token tidak ditemukan"
}
```


### GET `/membership/payment`

Lihat pembayaran membership

**Header:**
```
Authorization: Bearer {token} 
```

**Request Body:**
```json
{
    "error": false,
    "data": {
        "id_pembayaranMembership": 17,
        "id_membership": 17,
        "bank_pengirim": "BCA",
        "waktu_transfer": null,
        "jumlah_transfer": 50000,
        "status_pembayaran": "Pending",
        "bukti_transfer": null,
        "created_at": "2025-06-22T09:13:04.000000Z",
        "updated_at": "2025-06-22T09:13:04.000000Z",
        "membership": {
            "id_membership": 17,
            "id_pelanggan": 25,
            "tanggal_pembelian": "2025-06-22 16:13:04",
            "tanggal_mulai": null,
            "tanggal_berakhir": null,
            "status": "Pending",
            "created_at": "2025-06-22T09:13:04.000000Z",
            "updated_at": "2025-06-22T09:13:04.000000Z"
        }
    }
}

**Response error:**
```json
{
    "message": "Token tidak ditemukan"
}
```

### GET `/membership/upload-bukti/{idPembayaranMembership}`
```
Upload bukti bayar pembayaran membership
```
**Header:**
```
Authorization: Bearer {token} 
Content-Type: multipart/form-data
```
Upload bukti bayar membership

**Request Body:**

* `bukti_transfer`: file gambar (jpg, jpeg, png) *

**Response Sukses:**
```json
{
    "error": false,
    "message": "Bukti transfer berhasil diupload. Menunggu verifikasi.",
    "urlBuktiTransferMembership": "path_to_file",
    "waktuTransfer": "2025-06-22 16:34:22"
}
```

**Response error 401:**
```json
{
    "message": "Token tidak ditemukan"
}
```


### GET `/membership/current`
```
Lihat membership aktif
```
**Header:**
```
Authorization: Bearer {token} 
```

**Response Sukses:**
```json
{
    "error": false,
    "data": {
        "idMembership": 17,
        "tanggalMulai": "2025-06-22 16:39:59",
        "tanggalBerakhir": "2025-06-22 16:49:59",
        "statusMembership": "Aktif",
        "pembayaranMembership": {
            "idpembayaranMembership": 17,
            "bankPengirim": "BCA",
            "jumlahTransfer": 50000,
            "statusPembayaranMembership": "Berhasil",
            "buktiTransfer": "bukti_membership_Pr43BldUlW8GIkm3hAR1.jpg"
        }
    }
}
```

**Response error 401:**
```json
{
    "error": false,
    "message": "Tidak ada membership aktif.",
    "data": null
}
```

##  5. **Event**
### GET `/event`

Melihat list Event

**Response Sukses:**
```json
{
    "error": false,
    "message": "Daftar event berhasil diambil",
    "listEvent": [
        {
            "idEvent": 1,
            "namaEvent": "3D Digital Printing",
            "jadwalEvent": "2025-04-01",
            "fotoEvent": "path_to_file",
            "deskripsiEvent": "Belajar membuat karakter maskot 3D sesuai dengan pilihan daerah favoritmu",
            "kuota": 7,
            "hargaEvent": 50000,
            "fotoKegiatan": [
                "path_to_file"
            ]
        },
        .....
    ],
    "status": "sukses"
}
```
### GET `/event/{idEvent}`
Melihat 1 Event 
**Response Sukses:**
```json
{
    "statusCode": 200,
    "error": false,
    "message": "Detail event berhasil diambil",
    "detailEvent": {
        "idEvent": 1,
        "namaEvent": "3D Digital Printing",
        "jadwalEvent": "2025-04-01",
        "fotoEvent": "path_to_file",
        "deskripsiEvent": "Belajar membuat karakter maskot 3D sesuai dengan pilihan daerah favoritmu",
        "kuota": 7,
        "hargaEvent": 50000,
        "fotoKegiatan": [
            "path_to_file"
        ]
    },
    "status": "sukses"
}
```

**Response error 404:**
```json
{
    "statusCode": 404,
    "error": true,
    "message": "Event tidak ditemukan",
    "detailEvent": null,
    "status": "gagal"
}
```
### GET `/event/detail-event`

Lihat foto kegiatan event

**Response sukses:**
```json
{
    "error": false,
    "message": "List foto kegiatan",
    "detailEventList": [
        {
            "idDetailEvent": 1,
            "idEvent": 1,
            "namaEvent": "3D Digital Printing",
            "fotoKegiatan": "path_to_file"
        },
        .....
    ]
}
```
### GET `/event/detail-event/{idEvent}`

Lihat 1 Foto Kegiatan event

**Response sukses:**
```json
{
    "error": false,
    "message": "Foto kegiatan berhasil diambil",
    "fotoKegiatan": [
        {
            "idDetailEvent": 1,
            "idEvent": 1,
            "fotoKegiatan": "path_to_file"
        }
    ]
}
```
**Response kalo gada deatil event utk id tsb:**
```json
{
    "error": true,
    "message": "Tidak ada foto kegiatan untuk event ini.",
    "fotoKegiatan": []
}
```
### POST `/event/cart`

Membeli event (masuk ke cart)

**Header:**
```
Authorization: Bearer {token} 
Content-Type: application/json
```
**Request body:**
```json
{
  "itemsEvent": [
    {
      "idEvent": 4,
      "jumlahTiket": 1
    },
    {
      "idEvent": 5,
      "jumlahTiket": 1
    }
  ]
}

```

**Response sukse:**
```json
{
    "error": false,
    "message": "Cart berhasil dibuat",
    "idPembelianEvent": 48,
    "totalHargaEvent": 170000,
    "cartEventItem": [
        {
            "idEvent": 4,
            "namaEvent": "Programmer Cilik",
            "hargaEvent": 100000,
            "jumlahTiket": 1,
            "subtotalEvent": 100000
        },
        {
            "idEvent": 5,
            "namaEvent": "Kreasi Sablon",
            "hargaEvent": 70000,
            "jumlahTiket": 1,
            "subtotalEvent": 70000
        }
    ]
}
```

**Response error 422:**
```json
{
    "error": true,
    "message": "Format itemsEvent tidak valid. Harus array."
}
```
### GET `/event/cart/listcart`
**Header:**

Lihat isi cart 

```
Authorization: Bearer {token} 
```

```json
{
    "error": false,
    "listCart": [
        {
            "idPembelianEvent": 70,
            "totalPembelianEvent": 250000,
            "tanggalPembelianEvent": null,
            "statusPembelianEvent": "Belum Checkout",
            "cartEventItem": [
                {
                    "idDetailPembelianEvent": 1,
                    "namaEvent": "3D Digital Printing",
                    "hargaEvent": 50000,
                    "jadwalEvent": "23-06-2025",
                    "jumlahTiket": 3,
                    "subtotalEvent": 150000
                },
                {
                    "idDetailPembelianEvent": 2,
                    "namaEvent": "Programmer Cilik",
                    "hargaEvent": 100000,
                    "jadwalEvent": "23-06-2025",
                    "jumlahTiket": 1,
                    "subtotalEvent": 100000
                },
                ......
            ]
        },
        {
            "idPembelianEvent": 71,
            "totalPembelianEvent": 150000,
            "tanggalPembelianEvent": null,
            "statusPembelianEvent": "Belum Checkout",
            "cartEventItem": [
                {
                    "idDetailPembelianEvent": 3,
                    "namaEvent": "3D Digital Printing",
                    "hargaEvent": 50000,
                    "jadwalEvent": "23-06-2025",
                    "jumlahTiket": 3,
                    "subtotalEvent": 150000
                }
            ]
        }
    ]
}
```
### GET `/event/cart/{idPembelianEvent}`
Lihat detail pembelian di cart
**Header:**
```
Authorization: Bearer {token} 
```
**Response Sukses:**
```json
{
    "error": false,
    "cartDetail": {
        "idPembelianEvent": 73,
        "totalHargaEvent": 200000,
        "statusPembelianEvent": "Belum Checkout",
        "cartEventItem": [
            {
                "idDetailPembelianEvent": 92,
                "idEvent": 4,
                "namaEvent": "Programmer Cilik",
                "hargaEvent": 100000,
                "jumlahTiket": 1,
                "subtotalEvent": 100000
            },
            {
                "idDetailPembelianEvent": 93,
                "idEvent": 1,
                "namaEvent": "3D Digital Printing",
                "hargaEvent": 50000,
                "jumlahTiket": 2,
                "subtotalEvent": 100000
            }
        ]
    }
}
```
**Response error 404:**
```json
{
    "error": true,
    "message": "Cart tidak ditemukan atau kosong."
}
```
### DELETE `/event/cart/{idPembelianEvent}`
Delete salah satu pembelian

**Header:**
```
Authorization: Bearer {token} 
```
**Response Sukses:**
```json
{
    "error": false,
    "message": "Cart berhasil dihapus."
}
```
**Response error 404:**
```json
{
    "error": true,
    "message": "Cart tidak ditemukan atau sudah checkout."
}
```
### POST `/event/checkout/{idPembelianEvent}`

Checkout salah satu pembelian

**Header:**
```
Authorization: Bearer {token} 
Content-Type: application/json
```

**Response sukses:**
```json
{
    "error": false,
    "message": "Checkout berhasil",
    "pembelianEventResponse": {
        "idPembelianEvent": 73,
        "totalHargaEvent": 200000,
        "statusPembelianEvent": "Belum bayar",
        "tanggalPembelianEvent": "2025-06-23T06:28:51.453992Z",
        "detailEvent": [
            {
                "idDetailPembelianEvent": 92,
                "namaEvent": "Programmer Cilik",
                "jumlahTiket": 1,
                "harga_event": 100000,
                "subtotal_event": 100000
            },
            .....
        ]
    }
}
```

**Response gagal:**
```json
{
    "error": true,
    "message": "Checkout gagal: Checkout gagal: Data pembelian atau event tidak ditemukan."
}
```
### POST `/event/pembayaran/pilih-bank`

Memilih bank untuk pembayaran tiket event. Pembelian yang dibayar adalah pembelian yang dibuat paling lama

**Header:**
```
Authorization: Bearer {token} 
Content-Type: application/json
```

**Request body:**
```json
{
    "bankPengirim": "BCA"
}
```

**Response success:**
```json
{
    "error": false,
    "message": "Bank berhasil dipilih. Silakan lakukan transfer manual.",
    "dataPembayaranEvent": {
        "idPembayaranEvent": 33,
        "idPembelianEvent": 72,
        "statusPembayaranEvent": "Menunggu Pembayaran",
        "bankPengirim": "DANA",
        "totalHargaEvent": 200000
    }
}
```
**Response error 404**
```json
{
    "error": true,
    "message": "Tidak ada pembelian event aktif atau pembayaran sudah dibuat."
}
```
**Response error 401**
```json
{
    "message": "Token tidak ditemukan"
}
```
### GET `/event/pembayaran/{idPembelianEvent}`

Melihat detail pembayaran pelanggan sesuai token

**Header:**
```
Authorization: Bearer {token} 
```
**Response success:**
```json
{
    "error": false,
    "detailBayarEvent": {
        "idPembayaranEvent": 33,
        "idPembelianEvent": 72,
        "totalHargaEvent": 200000,
        "tanggalBayarEvent": "tergantung, kalo udh bayar muncul kalo nga null",
        "statusPembayaranEvent": "Menunggu Pembayaran",
        "bankEvent": "DANA",
        "detailEvent": [
            {
                "namaEvent": "Programmer Cilik",
                "jumlahTiket": 1,
                "hargaEvent": 100000,
                "subtotalEvent": 100000
            },
            {
                "namaEvent": "3D Digital Printing",
                "jumlahTiket": 2,
                "hargaEvent": 50000,
                "subtotalEvent": 100000
            }
        ]
    }
}
```
**Response error 404:**
```json
{
    "error": true,
    "message": "Data pembelian tidak ditemukan atau bukan milik Anda"
}
```
### POST `/event/pembayaran/{idPembayaranEvent}/upload-bukti`

Upload bukti bayar event

**Header:**
```
Authorization: Bearer {token} 
Content-Type: multipart/form-data
```
**Request Body:**
* `bukti_transfer`: file gambar (jpg, jpeg, png)

**Response success:**
```json
{
    "error": false,
    "message": "Bukti pembayaran berhasil diupload.",
    "urlBuktiBayarEvent": "http://localhost:8000/storage/bukti-event/buktiBayar_event685809371e2ed.jpg"
}
```
**Response error 401:**
```json
{
    "message": "Token tidak valid atau kedaluwarsa"
}
```
### GET `/event/nota/{idPembelianEvent}`

**Header:**
```
Authorization: Bearer {token} 
```

**Response success:**
```json
{
    "error": false,
    "notaPembelianEvent": {
        "idPembelianEvent": 72,
        "idPembayaranEvent": 33,
        "tanggalPembelianEvent": "2025-06-23 13:30:24",
        "namaPelanggan": "grumpyayee",
        "teleponPelanggan": "081234567890",
        "emailPelanggan": "grumpyayeee@gmail.com",
        "totalPembelianEvent": 200000,
        "statusPembelianEvent": "Belum bayar",
        "statusPembayaranEvent": "Menunggu Pembayaran",
        "detailEvent": [
            {
                "no": 1,
                "namaEvent": "Programmer Cilik",
                "hargaEvent": 100000,
                "jadwalEvent": "23-06-2025",
                "jumlahTiket": 1,
                "subtotalEvent": 100000
            },
            ......
        ]
    }
}
```
**Response error 404:**
```json
{
    "error": true,
    "message": "Data pembelian tidak ditemukan atau bukan milik Anda"
}
```

## 6. **Merchandise**
### GET `/merch`

Melihat list merch

**Response Sukses:**
```json
{
    "error": false,
    "message": "Daftar merchandise berhasil diambil",
    "listMerchandise": [
        {
            "idMerchandise": 1,
            "namaMerchandise": "Cap Pink",
            "hargaMerchandise": 20000,
            "deskripsiMerchandise": "Topi kasual warna pink lucu",
            "stok": 10,
            "fotoMerchandise": "path_to_file"
        },
        .....
    ],
    "status": "sukses"
}
```
### GET `/merch/{idMerch}`

Melihat 1 merch

**Response Sukses:**
```json
{
    "error": false,
    "message": "Detail merchandise berhasil diambil",
    "detailMerchandise": {
        "idMerchandise": 1,
        "namaMerchandise": "Cap Pink",
        "hargaMerchandise": 20000,
        "deskripsiMerchandise": "Topi kasual warna pink lucu",
        "stok": 10,
        "fotoMerchandise": "path_to_file"
    }
}
```
**Response error 404:**
```json
{
    "error": true,
    "message": "Merchandise tidak ditemukan"
}
```