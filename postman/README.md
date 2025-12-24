Postman collection for api-reference

Import

1. In Postman, choose Import → File and select `postman/api-reference.postman_collection.json`.
2. Make sure you already have an Environment with the variables `host` and `token`.
   - `host` should be like `http://localhost:8000` (do not include a trailing slash).
   - `token` should contain the JWT string (without `Bearer ` prefix).

Usage

- Authenticate by calling `Auth > Login` with a valid user; copy the returned token into your environment `token` variable.
- All protected requests include a header `Authorization: Bearer {{token}}`.
- Path variables used by the collection:
  - `noteId` for notes endpoints
  - `channelId` for channels
  - `userId` for users

Notes

- The collection uses `{{host}}/api/...` for all URLs. Ensure your environment's `host` is correct.
- If you prefer, you can add a pre-request script to set the `Authorization` header globally using the environment `token` variable.
