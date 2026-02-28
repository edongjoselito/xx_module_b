# xx_module_b

## JSON API (GET)

- `GET /api/products`
  - Returns all visible products as JSON.
- `GET /api/products/{gtin}`
  - Returns one visible product by GTIN as JSON.

Examples:

```bash
curl http://localhost/xx_module_b/api/products
curl http://localhost/xx_module_b/api/products/1234567891234
```
