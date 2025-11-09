# API Documentation

## Overview
This document describes the HTTP API exposed by this Laravel application and the console import command used to populate postal codes.

Base URL (when running locally): http://localhost
API prefix: `/api`

Authentication: Laravel Sanctum token-based. Use the `Authorization: Bearer {token}` header for protected endpoints.

## Endpoints

All responses are JSON unless otherwise noted.

### POST /api/login
Authenticate a user and receive a Sanctum token.

Request (application/json):
{
  "email": "user@example.com",
  "password": "secret"
}

Success response (200):
{
  "token": "<plain-text-token>"
}

Error (invalid credentials): 401
{
  "message": "Invalid credentials"
}

### POST /api/register
Register a new user and receive a Sanctum token.

Request (application/json):
{
  "name": "Full Name",
  "email": "user@example.com",
  "password": "secret"
}

Success response (201):
{
  "user": { /* user object */ },
  "token": "<plain-text-token>"
}


### GET /api/user
Get the authenticated user.

Headers: Authorization: Bearer {token}

Success (200): user JSON

Middleware: `auth:sanctum`


### Postal codes collection

These endpoints are handled by `App\Http\Controllers\PostalCodeController` and operate on the `postal_codes` table.
Model fields (fillable): `zip`, `city`, `county`.
Migration creates columns: `id`, `zip` (string, indexed), `city`, `county` (nullable), timestamps. Unique constraint on [`zip`, `city`].


#### GET /api/postal-codes
Return all postal codes.

Response (200): JSON array of postal code objects.

Example:
[
  {
    "id": 1,
    "zip": "1000",
    "city": "Example City",
    "county": "Example County",
    "created_at": "...",
    "updated_at": "..."
  }
]


#### GET /api/postal-codes/{postal_code}
Query by the `zip` value (string). Returns a list (array) of matching records.

Response (200): array of objects (can be empty).


#### POST /api/postal-codes
Create a postal code record. Requires authentication (sanctum).

Headers: Authorization: Bearer {token}

Request (application/json):
{
  "zip": "1234",
  "city": "Budapest",
  "county": "Pest"
}

Validation: `zip` (required|string), `city` (required|string), `county` (required|string).

Success response (201): the created object


#### PUT /api/postal-codes/{postal_code}
Update first record that matches `zip` == {postal_code}. Requires auth.

Accepts partial updates (fields marked `sometimes|required`): `zip`, `city`, `county`.

Success (200): updated object
404 if not found.


#### DELETE /api/postal-codes/{postal_code}
Deletes the first record where `zip` == {postal_code}. Requires auth.

Success: 204 No Content (returned as an empty body with status 204)
404 if not found.


### Alternate routes by primary key, city, and county

The controller also provides operations by database `id`, by `city`, and by `county`.

- GET /api/id/{id} — get postal code by numeric `id`. Returns single object or 404.
- PUT /api/id/{id} — update by `id`. Same validation rules as other update endpoints.
- DELETE /api/id/{id} — delete by `id`.

- GET /api/city/{city} — get all postal codes matching `city` (exact match).
- PUT /api/city/{city} — update all postal codes with `city` == {city}. Request body uses same `zip`/`city`/`county` fields; returns updated records or 404.
- DELETE /api/city/{city} — delete all postal codes with `city` == {city}; returns 204 on success or 404 if none found.

- GET /api/county/{county} — get all postal codes matching `county`.
- PUT /api/county/{county} — update all postal codes with `county` == {county}; returns updated records or 404.
- DELETE /api/county/{county} — delete all postal codes with `county` == {county}; returns 204 on success or 404 if none found.

Notes:
- City and county matching is exact and case-sensitive depending on DB collation; queries use `where('city', $city)` etc.
- Bulk updates (PUT to city/county) return the set of updated records (re-read after update).


## Console import command

There is a console command to import postal codes from a CSV:

`php artisan import:postal-codes {path}`

Example (from repo root on Windows PowerShell):

```powershell
php artisan import:postal-codes "storage/app/iranyitoszamok.csv"
```

What it does:
- Expects a CSV file with a header row. The command implementation in `app/Console/Commands/ImportPostalCodes.php` reads the header and then reads rows.
- The command looks for a header named exactly `Irányítószám` and will skip rows where that field is empty.
- It maps CSV columns to the DB fields as:
  - CSV `Irányítószám` -> `zip`
  - CSV `Település` -> `city`
  - CSV `Megye` -> `county`
- The import command uses `PostalCode::create([...])` so it will insert rows directly. The file `storage/app/iranyitoszamok.csv` exists in the repo and can be used as a sample.

Caveat: there is also an import class `App\Imports\PostalCodesImport` (for Maatwebsite Excel) which maps English headers (`Postal Code`, `Place Name`, `County`) to different keys (`iranyitoszam`, `telepules`, `megye`). That class currently creates a `new PostalCode([...])` with keys that don't match the `PostalCode` model's `$fillable` (`zip`, `city`, `county`). The console command (above) is the active CSV importer and expects Hungarian header names. If you plan to use the `Maatwebsite\Excel` import class, review and align field names.


## Usage examples (curl)

Login and get token:

```bash
curl -X POST "http://localhost/api/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"secret"}'
```

Create a postal code (authenticated):

```bash
curl -X POST "http://localhost/api/postal-codes" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <token>" \
  -d '{"zip":"1234","city":"Kispest","county":"Pest"}'
```

Update by id:

```bash
curl -X PUT "http://localhost/api/id/1" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <token>" \
  -d '{"city":"Újváros"}'
```


## Errors and validations
- 400-level responses are returned for validation errors (Laravel default JSON structure).
- 401 when credentials are invalid or token missing/expired for protected endpoints.
- 404 when items are not found for the given id/zip/city/county.
- 204 No Content for successful deletions.


## Notes & recommended next steps
- If you expect to import CSVs with English headers, fix `App\Imports\PostalCodesImport` to use `zip`/`city`/`county` keys or adjust Maatwebsite import mapping.
- Consider adding pagination to `GET /api/postal-codes` if the dataset grows large.
- Add more detailed response schemas or API Resource classes for consistent responses.


## Files referenced
- `routes/api.php` — API route definitions
- `app/Http/Controllers/PostalCodeController.php` — controller logic and validation rules
- `app/Http/Controllers/AuthController.php` — login/register
- `app/Console/Commands/ImportPostalCodes.php` — CSV importer command
- `storage/app/iranyitoszamok.csv` — example CSV included in repo


---
Generated by repository inspection on 2025-10-20.
