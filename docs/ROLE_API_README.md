# Role Module API

These APIs are available under the protected Sanctum API prefix:

- Base URL: `/api/v1`
- Auth header: `Authorization: Bearer YOUR_SANCTUM_TOKEN`
- Content type: `Accept: application/json`

## 1. Create Role

- Method: `POST`
- URL: `/api/v1/roles`
- Permission required: `create_roles`

```bash
curl --location 'http://127.0.0.1:8000/api/v1/roles' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN' \
--header 'Content-Type: application/json' \
--data '{
  "name": "project-manager",
  "permissions": [1, 2, 3]
}'
```

## 2. Get Roles List With Permissions Count

- Method: `GET`
- URL: `/api/v1/roles`
- Permission required: `view_roles`

```bash
curl --location 'http://127.0.0.1:8000/api/v1/roles' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN'
```

## 3. Update Role

- Method: `PUT`
- URL: `/api/v1/roles/{id}`
- Permission required: `edit_roles`

```bash
curl --location --request PUT 'http://127.0.0.1:8000/api/v1/roles/1' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN' \
--header 'Content-Type: application/json' \
--data '{
  "name": "senior-project-manager",
  "permissions": [1, 4, 7]
}'
```

## 4. Delete Role By ID

- Method: `DELETE`
- URL: `/api/v1/roles/{id}`
- Permission required: `delete_roles`

```bash
curl --location --request DELETE 'http://127.0.0.1:8000/api/v1/roles/1' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN'
```

## 5. Delete All Roles

- Method: `DELETE`
- URL: `/api/v1/roles/delete-all`
- Permission required: `delete_roles`

```bash
curl --location --request DELETE 'http://127.0.0.1:8000/api/v1/roles/delete-all' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN'
```

## Response Format

Successful responses follow this structure:

```json
{
  "success": true,
  "message": "Role created successfully.",
  "data": {}
}
```

Validation and error responses follow this structure:

```json
{
  "success": false,
  "message": "Validation error.",
  "errors": {
    "name": [
      "The name field is required."
    ]
  }
}
```

## HTTP Status Codes

- `200` for successful fetch, update, delete, and delete-all requests
- `201` for successful create requests
- `401` for unauthenticated requests
- `403` for unauthorized requests
- `404` when the requested role does not exist
- `422` for validation failures
- `500` for unexpected server errors
