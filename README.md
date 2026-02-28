# xx_module_b

## JSON API (GET)

- `GET /api/products`
  - Returns all visible products as JSON.
- `GET /api/products/{gtin}`
  - Returns one visible product by GTIN as JSON.
- `GET /api/companies`
  - Returns active companies as JSON.
- `GET /api/companies?status=deactivated`
  - Returns deactivated companies as JSON.
- `GET /api/companies?status=all`
  - Returns all companies as JSON.
- `GET /api/companies/{id}`
  - Returns one company by ID as JSON.

Examples:

```bash
curl http://localhost/xx_module_b/api/products
curl http://localhost/xx_module_b/api/products/1234567891234
curl http://localhost/xx_module_b/api/companies
curl http://localhost/xx_module_b/api/companies/4
```
