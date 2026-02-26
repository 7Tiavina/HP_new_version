# Monetico Payment Fixes - February 26, 2026

## Issues Found and Fixed

### **Issue 1: Database Constraint Violations (SQLite)**

**Errors:**
```
SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: clients.password_hash
SQLSTATE[23000]: Integrity constraint violation: 19 FOREIGN KEY constraint failed
```

**Root Causes:**
1. The `clients` table was created with `password_hash` as NOT NULL, but guest users don't have passwords
2. The `commandes` table had a column named `user_id` but the code was using `client_id`
3. SQLite doesn't support `change()` operation like MySQL, requiring table recreation

**Files Modified:**
1. `database/migrations/2026_02_26_000002_fix_clients_password_hash_for_sqlite.php` - NEW migration to recreate clients table with nullable password_hash
2. `database/migrations/2026_02_26_000003_fix_commandes_client_id_foreign_key.php` - NEW migration to fix commandes table foreign key
3. `app/Models/Client.php` - Added boot method to handle null password_hash
4. `app/Http/Controllers/PaymentController.php` - Explicitly set password_hash to null for guests

**Fix:**
- Created SQLite-compatible migrations that recreate tables with correct schema
- Updated `Client` model to explicitly set `password_hash` to null when empty
- Updated `PaymentController` to pass `password_hash: null` when creating guest clients
- Fixed `commandes` table to use `client_id` foreign key instead of `user_id`
- Added `invoice_content` column as nullable JSON

---

### **Issue 2: Monetico Void API Endpoint Not Found**

**Error:**
```
errorCode: "INT_901"
errorMessage: "web-service not found"
```

**Root Cause:**
The Void payment endpoint varies between Monetico instances. Some use `/Charge/{uuid}/Void` while others use `/PaymentMethod/{uuid}/Void`.

**Files Modified:**
1. `app/Http/Controllers/PaymentController.php` - Enhanced `_moneticoVoidPayment()` method

**Fix:**
Implemented a fallback mechanism that tries both endpoints:
```php
// First try: /Charge/{transactionId}/Void
// If INT_901 error, fallback to: /PaymentMethod/{transactionId}/Void
```

---

### **Issue 3: Insufficient Logging**

**Root Cause:**
When payments failed, there wasn't enough logging to quickly identify:
- The exact point of failure
- The amounts being processed
- The transaction IDs at each step
- Whether void operations succeeded or failed

**Files Modified:**
1. `app/Http/Controllers/PaymentController.php` - Enhanced logging throughout

**Fix:**
Added comprehensive logging in the following areas:

#### a) Payment Creation (`redirectToMonetico`):
- Log request payload details (amount, currency, order ID, customer email)
- Log success with formToken length
- Log detailed error with full response when formToken is missing

#### b) Payment Capture (`paymentSuccess`):
- Log BDM order creation success with order ID
- Log capture attempt with transaction ID and amount
- Log capture response with full details
- **NEW:** Log detailed error when capture fails (status code, response body, JSON)

#### c) Void Operations:
- Log void attempt reason (BDM failure or exception)
- Log void response status and body
- **NEW:** Log critical error if void operation fails

#### d) Exception Handling:
- Log full stack trace
- Log void attempt details
- Log if void operation fails after exception

#### e) Client Creation:
- Log client creation/update with email and guest status
- Log successful client ID assignment

---

## Files Changed

### Modified Files:
1. `app/Models/Client.php`
2. `app/Http/Controllers/PaymentController.php`

### New Files:
1. `database/migrations/2026_02_26_000001_make_password_hash_nullable_in_clients_table.php`
2. `database/migrations/2026_02_26_000002_fix_clients_password_hash_for_sqlite.php`
3. `database/migrations/2026_02_26_000003_fix_commandes_client_id_foreign_key.php`

---

## Testing Recommendations

### Test Scenario 1: Guest User Payment
1. Navigate to the site as a non-authenticated user
2. Select baggage and options
3. Complete the payment form with test card details
4. Verify:
   - Payment completes successfully
   - Client record is created with `password_hash: null`
   - Order is created in BDM
   - Payment is captured in Monetico
   - Confirmation email is sent

### Test Scenario 2: Registered User Payment
1. Login as a registered user
2. Select baggage and options
3. Complete the payment form
4. Verify:
   - Payment completes successfully
   - Client record is updated (not duplicated)
   - Card details are saved to client profile

### Test Scenario 3: Payment Failure Handling
1. Use a test card that triggers a BDM error
2. Verify:
   - Void operation is called correctly
   - Void succeeds (no INT_901 error)
   - User is redirected back with appropriate error message
   - Logs show complete void operation details

### Test Scenario 4: Network/Connection Error
1. Simulate a network error during capture
2. Verify:
   - Exception is caught and logged with stack trace
   - Void operation is attempted
   - Void response is logged
   - User sees appropriate error message

---

## Log Locations

Check logs at: `storage/logs/laravel.log`

Key log entries to monitor:
- `[paymentSuccess] Creating/updating client`
- `[paymentSuccess] BDM order creation successful`
- `[paymentSuccess] Attempting to capture payment with Monetico`
- `[paymentSuccess] Monetico payment capture successful`
- `[paymentSuccess] Calling Monetico Void` (if failure occurs)

---

## Monetico API Endpoints Used

| Operation | Endpoint | Method |
|-----------|----------|--------|
| Create Payment Form | `/Charge/CreatePayment` | POST |
| Capture Payment | `/Charge/{transactionId}/Capture` | POST |
| Void Payment | `/PaymentMethod/{transactionId}/Void` | POST |

---

## Next Steps

1. **Monitor Logs**: Watch the logs for the next few payment transactions to ensure:
   - No more `password_hash` constraint violations
   - Void operations succeed when needed
   - Capture operations complete successfully

2. **Database Verification**: After first guest payment, verify:
   ```sql
   SELECT id, email, password_hash, nom, prenom 
   FROM clients 
   WHERE password_hash IS NULL 
   ORDER BY created_at DESC 
   LIMIT 5;
   ```

3. **Production Deployment**: Before deploying to production:
   - Run the migration on production database
   - Test with production Monetico credentials
   - Monitor logs closely for first few transactions

---

## Contact

If issues persist after these fixes, check:
1. Monetico API credentials in `.env` file
2. BDM API credentials and service availability
3. Network connectivity to both APIs
4. Server time synchronization (important for API authentication)
