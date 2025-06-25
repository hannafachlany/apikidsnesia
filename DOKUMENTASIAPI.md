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
        "idMembership": 24,
        "idPembayaranMembership": 24,
        "tanggalPembelian": "2025-06-24 20:31:20",
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
            "tanggalEvent": "10-07-2025",
            "jadwalEvent": "09:00",
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
        "tanggalEvent": "10-07-2025",
        "jadwalEvent": "09:00",
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

**Response sukses:**
```json
{
    "error": false,
    "message": "Cart berhasil dibuat",
    "pembelianEventResponse": {
        "idPembelianEvent": 89,
        "tanggalPembelianEvent": null,
        "totalHargaEvent": 150000,
        "statusPembelianEvent": "Belum Checkout",
        "cartEventItem": [
            {
                "idDetailPembelianEvent": 110,
                "idEvent": 1,
                "namaEvent": "3D Digital Printing",
                "hargaEvent": 50000,
                "jumlahTiket": 3,
                "subtotalEvent": 150000
            }
        ]
    }
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
    "listEventCart": [
        {
            "idPembelianEvent": 86,
            "totalPembelianEvent": 150000,
            "tanggalPembelianEvent": null,
            "statusPembelianEvent": "Belum Checkout",
            "cartEventItem": [
                {
                    "idDetailPembelianEvent": 107,
                    "fotoEvent": "path_to_file",
                    "idEvent": 1,
                    "namaEvent": "3D Digital Printing",
                    "hargaEvent": 50000,
                    "tanggalEvent": "10-07-2025",
                    "jadwalEvent": "09:00",
                    "jumlahTiket": 3,
                    "subtotalEvent": 150000
                },....
            ]
        },
        {
            "idPembelianEvent": 87,
            "totalPembelianEvent": 150000,
            "tanggalPembelianEvent": null,
            "statusPembelianEvent": "Belum Checkout",
            "cartEventItem": [
                {
                    "idDetailPembelianEvent": 108,
                    "fotoEvent": "path_to_file",
                    "idEvent": 1,
                    "namaEvent": "3D Digital Printing",
                    "hargaEvent": 50000,
                    "tanggalEvent": "10-07-2025",
                    "jadwalEvent": "09:00",
                    "jumlahTiket": 3,
                    "subtotalEvent": 150000
                },....
            ]
        },....
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
    "cartEventDetail": {
        "idPembelianEvent": 85,
        "tanggalPembelianEvent": null,
        "totalHargaEvent": 150000,
        "statusPembelianEvent": "Belum Checkout",
        "cartEventItem": [
            {
                "idDetailPembelianEvent": 106,
                "fotoEvent": "path_to_file",
                "idEvent": 1,
                "namaEvent": "3D Digital Printing",
                "hargaEvent": 50000,
                "tanggalEvent": "10-07-2025",
                "jadwalEvent": "09:00",
                "jumlahTiket": 3,
                "subtotalEvent": 150000
            },....
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
Accept : application/json
```

**Response sukses:**
```json
{
    "error": false,
    "message": "Checkout berhasil",
    "pembelianEventResponse": {
        "idPembelianEvent": 85,
        "totaHargalEvent": 150000,
        "statusPembelianEvent": "Belum bayar",
        "tanggalPembelianEvent": "2025-06-23T09:29:57.486738Z",
        "cartEventItem": [
            {
                "idDetailPembelianEvent": 106,
                "fotoEvent": "path_to_file",
                "idEvent": 1,
                "namaEvent": "3D Digital Printing",
                "jumlahTiket": 3,
                "tanggalEvent": "10-07-2025",
                "jadwalEvent": "09:00",
                "hargaEvent": 50000,
                "subtotalEvent": 150000
            },....
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
        "idPembayaranEvent": 34,
        "idPembelianEvent": 90,
        "statusPembayaranEvent": "Menunggu Pembayaran",
        "bankPengirim": "Mandiri",
        "totalHargaEvent": 150000
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
        "idPembayaranEvent": 34,
        "idPembelianEvent": 90,
        "totalHargaEvent": 150000,
        "tanggalBayarEvent": null,
        "statusPembayaranEvent": "Menunggu Pembayaran",
        "bankEvent": "Mandiri",
        "detailEvent": [
            {
                "idEvent": 1,
                "idDetailPembelianEvent": 111,
                "namaEvent": "3D Digital Printing",
                "tanggalEvent": "10-07-2025",
                "jadwalEvent": "09:00",
                "jumlahTiket": 3,
                "hargaEvent": 50000,
                "subtotalEvent": 150000
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
* `buktiBayarEvent`: file gambar (jpg, jpeg, png)

**Response success:**
```json
{
    "error": false,
    "message": "Bukti pembayaran berhasil diupload.",
    "urlBuktiBayarEvent": "path_to_file"
}
```
**Response error 401:**
```json
{
    "message": "Token tidak valid atau kedaluwarsa"
}
```

### GET `/nota-event`
**Header:**
```
Authorization: Bearer {token} 
```

**Response success:**
```json
{
    "error": false,
    "listNotaPembelianEvent": [
        {
            "idPembelianEvent": 94,
            "tanggalPembelianEvent": "2025-06-23 20:53:01",
            "totalPembelianEvent": 100000,
            "statusPembelianEvent": "Belum bayar",
            "statusPembayaranEvent": "Menunggu Verifikasi"
        },
        {
            "idPembelianEvent": 90,
            "tanggalPembelianEvent": "2025-06-23 16:49:54",
            "totalPembelianEvent": 150000,
            "statusPembelianEvent": "Berhasil",
            "statusPembayaranEvent": "Berhasil"
        }
    ]
}
```


### GET `/nota-event/{idPembelianEvent}`

**Header:**
```
Authorization: Bearer {token} 
```

**Response success:**
```json
{
    "error": false,
    "notaPembelianEvent": {
        "idPembelianEvent": 90,
        "idPembayaranEvent": 34,
        "tanggalPembelianEvent": "2025-06-23 16:49:54",
        "namaPelanggan": "grumpyayee",
        "teleponPelanggan": "081234567890",
        "emailPelanggan": "grumpyayeee@gmail.com",
        "totalPembelianEvent": 150000,
        "statusPembelianEvent": "Belum bayar",
        "statusPembayaranEvent": "Menunggu Pembayaran",
        "detailEvent": [
            {
                "idDetailPembelianEvent": 111,
                "idEvent": 1,
                "namaEvent": "3D Digital Printing",
                "hargaEvent": 50000,
                "tanggalEvent": "10-07-2025",
                "jadwalEvent": "09:00",
                "jumlahTiket": 3,
                "subtotalEvent": 150000
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

### POST `/merch/cart`
**Header**
```
Authorization: Bearer {token} 
Content-type: application/json
```

Masukin merch yg ingin dibeli ke cart

**Request Body:**
```json
{
  "itemsMerch": [
     {
      "idMerch": 1,
      "jumlah": 2
    },....
  ]
}
```

**Response sukses:**
```json
{
    "error": false,
    "message": "Cart berhasil dibuat",
    "pembelianMerchResponse": {
        "idPembelianMerch": 43,
        "tanggalPembelianMerch": null,
        "totalHargaMerch": 40000,
        "statusPembelianMerch": "Cart",
        "cartMerchItem": [
            {
                "idDetailPembelianMerch": 56,
                "idMerch": 1,
                "namaMerch": "Cap Pink",
                "jumlahMerch": 2,
                "hargaMerch": 20000,
                "subtotalMerch": 40000
            },....
        ]
    }
}
```

**Respon error 422:**
```json
{
    "error": true,
    "message": "Gagal membuat cart: format request itemsMerch harus array"
}
```

### GET `/merch/cart/listcart`
**Header**
```
Authorization: Bearer {token} 
```

liat isi cart merch

**Respon sukses**:
```json
{
    "error": false,
    "listCartMerch": [
        {
            "idPembelianMerch": 45,
            "tanggalPembelianMerch": null,
            "totalHargaMerch": 40000,
            "statusPembelianMerch": "Cart",
            "cartMerchItem": [
                {
                    "idDetailPembelianMerch": 58,
                    "fotoMerchandise": "path_to_file",
                    "idMerch": 2,
                    "namaMerch": "Cap Biru",
                    "jumlahMerch": 2,
                    "hargaMerch": 20000,
                    "subtotalMerch": 40000
                }
            ]
        },
        {
            "idPembelianMerch": 46,
            "tanggalPembelianMerch": null,
            "totalHargaMerch": 40000,
            "statusPembelianMerch": "Cart",
            "cartMerchItem": [
                {
                    "idDetailPembelianMerch": 59,
                    "fotoMerchandise": "path_to_file",
                    "idMerch": 2,
                    "namaMerch": "Cap Biru",
                    "jumlahMerch": 2,
                    "hargaMerch": 20000,
                    "subtotalMerch": 40000
                }
            ]
        }
    ]
}
```

### GET `/merch/cart/{idPembelianMerch}`

Liat isi detail cart merch

**Header**
```
Authorization: Bearer {token} 
```


**Respon sukses:**
```json
{
    "error": false,
    "itemMerchCart": {
        "idPembelianMerch": 35,
        "tanggalPembelianMerch": null,
        "totalHargaMerch": 2020000,
        "statusPembelianMerch": "Cart",
        "cartMerchItem": [
            {
                "idDetailPembelianMerch": 48,
                "fotoMerchandise": "path_to_file",
                "idMerch": 1,
                "namaMerch": "Cap Pink",
                "jumlahMerch": 100,
                "hargaMerch": 20000,
                "subtotalMerch": 2000000
            },
            {
                "idDetailPembelianMerch": 49,
                "fotoMerchandise": "path_to_file",
                "idMerch": 2,
                "namaMerch": "Cap Biru",
                "jumlahMerch": 1,
                "hargaMerch": 20000,
                "subtotalMerch": 20000.00
            }
        ]
    }
}
```

**Respon error 404:**
```json
{
    "error": true,
    "message": "Detail pembelian tidak ditemukan atau bukan punya anda"
}
```



### POST `/merch/checkout/{idPembelianMerch}`

Checkout salah satu pembelian merch

**Header**
```
Authorization: Bearer {token} 
Content-Type: application/json
Accept: application/json
```

**Respon Sukses**
```json
{
    "error": false,
    "message": "Checkout berhasil",
    "pembelianMerchResponse": {
        "idPembelianMerch": 35,
        "tanggalPembelianMerch": "2025-06-23T09:28:21.241789Z",
        "totalHargaMerch": 40000,
        "statusPembelianMerch": "Belum Bayar",
        "cartMerchItem": [
            {
                "idDetailPembelianMerch": 48,
                "fotoMerchandise": "path_to_file",
                "idMerch": 1,
                "namaMerch": "Cap Pink",
                "jumlahMerch": 2,
                "hargaMerch": 20000,
                "subtotalMerch": 40000
            },....
        ]
    }
}
```

**Respon error 404:**
```json
{
    "error": true,
    "message": "Data pembelian tidak ditemukan atau tidak valid."
}
```


### POST `/merch/pembayaran/pilih-bank`


Pelanggan memilih bank utk transfer

**Header**
```
Authorization: Bearer {token} 
Content-type: application/json
Accept: Content-type: application/json
```

**Request body:**
{
  "bankPengirim": "BCA Syariah"
}


**Respon sukses:**
```json
{
    "error": false,
    "message": "Bank berhasil dipilih. Silakan transfer manual.",
    "dataPembayaranMerch": {
        "idPembayaranMerch": 15,
        "idPembelianMerch": 44,
        "statusPembayaranMerch": "Menunggu Pembayaran",
        "bankPengirim": "BCA Syariah",
        "totalHargaMerch": 40000
    }
}
```
**Respon error 404:**
```json
{
    "error": true,
    "message": "Tidak ada pembelian aktif atau pembayaran sudah dibuat."
}
```


### GET `/merch/pembayaran/{idPembelianMerch}`

Liat detail bayar

**Header:**
```
Authorization: Bearer {token} 
```

**Response sukses**
```json
{
    "error": false,
    "detailBayarMerch": {
        "idPembayaranMerch": 15,
        "idPembelianMerch": 44,
        "totalHargaMerch": "40000.00",
        "tanggalBayarMerch": null,
        "statusPembayaranMerch": "Menunggu Pembayaran",
        "bankPengirim": "BCA Syariah",
        "detailMerch": [
            {
                "idMerchandise": 1,
                "idDetailPembelianMerchandise": 57,
                "namaMerch": "Cap Pink",
                "jumlahMerch": 2,
                "hargaMerch": 20000,
                "subtotalMerch": 40000
            }
        ]
    }
}
```

### POST `merch/pembayaran/{idPembayaranMerch}/upload-bukti`

Upload bukti transfer merchandise

**Header:**
```
Authorization: Bearer {token} 
Content-Type: multipart/form-data
```

**Request Body:**
* `buktiBayarMerch`: file gambar (jpg, jpeg, png)

**Respon sukses:**
```json
{
    "error": false,
    "message": "Bukti pembayaran berhasil diupload.",
    "urlBuktiBayarMerch": "http://localhost:8000/storage/bukti-merch/buktiBayar_merch_68592d377f7d4.jpg"
}
```


### GET `/merch/nota-merch`

lihat list nota pembelian merch

**Header:**
```
Authorization: Bearer {token} 

```

**Response sukses:**
```json
{
    "error": false,
    "listNotaPembelianMerch": [
        {
            "idPembelianMerch": 46,
            "tanggalPembelianMerch": "2025-06-24 12:49:12",
            "totalPembelianMerch": 180000,
            "statusPembelianMerch": "Belum Bayar",
            "statusPembayaranMerch": "Menunggu Pembayaran"
        },
        {
            "idPembelianMerch": 45,
            "tanggalPembelianMerch": "2025-06-24 12:48:04",
            "totalPembelianMerch": 90000,
            "statusPembelianMerch": "Belum Bayar",
            "statusPembayaranMerch": "Menunggu Pembayaran"
        }
    ]
}
```



### GET `/merch/nota-merch/{idPembelianMerch}`

lihat detail nota

**Header:**
```
Authorization: Bearer {token} 

```
**Respon sukses**
```json
{
    "error": false,
    "notaPembelianMerch": {
        "idPembelianMerch": 45,
        "idPembayaranMerch": 16,
        "tanggalPembelianMerch": "2025-06-24 12:48:04",
        "namaPelanggan": "grumpyayee",
        "teleponPelanggan": "081234567890",
        "emailPelanggan": "grumpyayeee@gmail.com",
        "totalPembelianMerch": 90000,
        "statusPembelianMerch": "Belum Bayar",
        "statusPembayaranMerch": "Menunggu Pembayaran",
        "detailMerch": [
            {
                "idDetailPembelianMerch": 45,
                "idMerch": 2,
                "namaMerch": "Cap Kidsnesia - Biru",
                "hargaMerch": 20000,
                "jumlahMerch": 2,
                "subtotalMerch": 90000
            }
        ]
    }
}
```

**Respon error 404:**
```json
{
    "error": true,
    "message": "Nota tidak ditemukan"
}
```


## 7. **Video**
### GET `/videos`
**Header:**
```
Authorization: Bearer {token} 
```

Melihat list video

**Response sukses:**
```json
{
    "error": false,
    "message": "List video berhasil diambil.",
    "data": [
        {
            "idVideo": 4,
            "judulVideo": "Belajar Sambil Bermain Mengenal Keunikan Budaya Indonesia Kepada Anak, Wisata Edukasi Kidsnesia",
            "deskripsiVideo": "Menjelajahi Keindahan dan Keberagaman Budaya Nusantara Melalui......",
            "filePath": "http://localhost:8000/storage/XbdIvD7KmE6l3d3jbknF4yIBOtNpcuzmIr6nKnz2.mp4"
        },
        {
            "idVideo": 6,
            "judulVideo": "Belajar Adab & Akhlak - Keutamaan Saling Memberi Hadiah",
            "deskripsiVideo": "Memberi hadiah kepada saudara bukan sekadar tradisi, melainkan cara untuk mempererat hubungan dan menunjukkan.........",
            "filePath": "http://localhost:8000/storage/XFLjWfLPTWFjHuxKEjh6e42cblER5lwBRgoe9aMY.mp4"
        },
        {
            "idVideo": 7,
            "judulVideo": "Belajar Sambil Bermain Tentang Adab dan Akhlak Anak Indonesia",
            "deskripsiVideo": "Kidsnesia adalah destinasi wisata edukasi yang dirancang.....",
            "filePath": "http://localhost:8000/storage/RDZymGJXOUVwLtyAjkbEc4WRBuDYu6MNNcBcQmNM.mp4"
        }
    ]
}
```
### GET `/videos/{idVideo}`
**Header:**
```
Authorization: Bearer {token} 
```

Melihat detail video

**Response sukses:**
```json
{
    "error": false,
    "message": "Detail video berhasil diambil.",
    "detailVideo": {
        "idVideo": 6,
        "judulVideo": "Belajar Adab & Akhlak - Keutamaan Saling Memberi Hadiah",
        "deskripsiVideo": "Memberi hadiah kepada saudara bukan sekadar tradisi, melainkan.....",
        "filePath": "http://localhost:8000/storage/XFLjWfLPTWFjHuxKEjh6e42cblER5lwBRgoe9aMY.mp4"
    }
}
```


