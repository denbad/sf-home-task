## Demo Symfony app to conduct payments

### Minimal command set to make it run:

```shell
git clone git@github.com:denbad/sf-home-task.git demo
```
```shell
cd demo
```
```shell
./run init
```
```shell
./run vendors
```
```shell
./run test
```
```shell
./run start
```
```shell
./run bin/console messenger:consume application_events domain_events -vvv
```
```shell
curl -k https://demo.baboon.localhost/api/payment?firstname=James&lastname=Bond&paymentDate=2022-12-12T15%3A19%3A21%2B00%3A00&amount=399.99&description=LN20221212&refId=130f8a89-51c9-47d0-a6ef-1aea54924d3a
```
```shell
./run bin/console app:payments:export --file=payments-4.csv
```
```shell
./run bin/console app:payments:list --date=Y-D-m
```

### Miscellaneous

Connect to mysql:

```shell
docker exec -it demo-database mysql -uroot -proot
```

Run any to composer command:

```shell
./run composer show -i
```

Clear application cache:

```shell
./run cc
```

### 1. Install

Clone from git repository:
```shell
git clone git@github.com:denbad/sf-home-task.git demo
```
```shell
cd demo
```

### 2. Pre-run

Warmup application:

```shell
./run init
```

(Optionally) Edit docker env variables to your specific needs:

```shell
.docker/.env
```

Install vendors:

```shell
./run vendors
```

### 3. Run

Start application:

```shell
./run start
```

Restart application:

```shell
./run restart
```

Stop application:

```shell
./run stop
```

Run unit tests:

```shell
./run test
```

Start background workers:

```shell
./run bin/console messenger:consume application_events domain_events -vvv
```

(Optionally) Stop workers:

```shell
./run bin/console messenger:stop-workers
```

### 4. Check installation

(Optionally) Check platform requirements:

```shell
./run check-platform-requirements
```

(Optionally) Check Symfony requirements:

```shell
./run check-symfony-requirements
```

(Optionally) Check security:

```shell
./run check-security
```

### 5. Conduct payment via browser / api

Head to:
```shell
https://demo.baboon.localhost/api/payment
```

Try conduct single payment via GET request:

```shell
https://demo.baboon.localhost/api/payment?firstname=James&lastname=Bond&paymentDate=2022-12-12T15%3A19%3A21%2B00%3A00&amount=399.99&description=LN20221212&refId=130f8a89-51c9-47d0-a6ef-1aea54924d3a
```

### 6. Conduct payment via command line

Try batch payments with csv file of your choice:

```shell
./run bin/console app:payments:export --file=payments-1.csv
./run bin/console app:payments:export --file=payments-2.csv
./run bin/console app:payments:export --file=payments-3.csv
./run bin/console app:payments:export --file=payments-4.csv
./run bin/console app:payments:export --file=foo.csv
```

### 7. List payments by date conducted

Run:
```shell
./run bin/console app:payments:list --date=2022-12-12
```
