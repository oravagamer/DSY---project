# DSY---project

Generate pk and certificate
```bash
.\openssl.exe genpkey -algorithm RSA -out private.key -aes256

set OPENSSL_CONF=C:\xampp\apache\conf\openssl.cnf

.\openssl.exe req -new -key private.key -out request.csr
```