# Creating Certificates

## CA Certificate

Create the root CA private key and public certificate:

```shell script
openssl genrsa -des3 -out rootCA.key 4096
openssl req -x509 -new -nodes -key rootCA.key -sha256 -days 100000 -out rootCA.crt
```

Add the public certificate to the system's root CA store:

On Linux:
```shell script
sudo cp rootCA.crt /usr/local/share/ca-certificates/BaboonCA.crt
sudo update-ca-certificates
```

On OSx:
```shell script
sudo security add-trusted-cert -d -r trustRoot -k "/Library/Keychains/System.keychain" rootCA.crt
sudo security verify-cert -c rootCA.crt
```

Don't forget to add the root CA certificate to Google Chrome or Firefox root CA store. 

## Server Certificate

Create the private key:

```shell script
openssl genrsa -out baboon.localhost.key 2048
```

Create the certificate request:

```shell script
openssl req -new -sha256 -key baboon.localhost.key -config baboon.localhost.conf -out baboon.localhost.csr
```

Sign the public certificate with the root CA key: 

```shell script
openssl x509 -req -in baboon.localhost.csr -CA rootCA.crt -CAkey rootCA.key -CAcreateserial -out baboon.localhost.crt -days 50000 -sha256 -extfile v3.ext
```
