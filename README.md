# SmartPay Developer Test

The requested changes are:
1. Add a field for the user to input their monthly gross income, and add check that the loan payment is not more than 15% of their monthly income
2. Display the APR (including the Origination Fee)
3. Add a collapsable amortization table (should show each payment's ( amount, interest, principal, and remaining balance)
4. Add a way to email the results as a pdf to the user

## Installation

Use composer to install packages

```bash
composer install
```

## Usage

Start Server using [Symfony's local server](https://symfony.com/doc/current/setup/symfony_server.html)

```bash
symfony server:start
```



## Unit Tests
```bash
./bin/phpunit
```
