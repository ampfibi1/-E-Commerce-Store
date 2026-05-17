# ShopHub — Demo Login Accounts

Local URL: <http://localhost/ecommerce/public/?c=auth&a=login>

| Role | Name | Email | Password | Dashboard |
|---|---|---|---|---|
| Admin | Admin | `admin@example.com` | `admin123` | <http://localhost/ecommerce/app/controllers/Admin_controller/dashboardController.php> |
| Seller | Tamjid | `tamjid@example.com` | `admin123` | <http://localhost/ecommerce/public/?c=seller&a=dashboard> |
| Seller | Mahin | `mahin@example.com` | `admin123` | <http://localhost/ecommerce/public/?c=seller&a=dashboard> |
| Customer | Ayesha | `ayesha@example.com` | `admin123` | <http://localhost/ecommerce/public/?c=customer&a=products> |
| Customer | Rashed | `rashed@example.com` | `admin123` | <http://localhost/ecommerce/public/?c=customer&a=products> |
| Delivery Manager | Delivery Manager | `delivery@shophub.com` | `delivery123` | <http://localhost/ecommerce/public/?c=delivery&a=dashboard> |

## Notes

- Customer / Seller / Admin passwords are stored in plaintext (`admin123`) — `loginController.php` does string compare.
- Delivery Manager password is bcrypt-hashed. Login will only work after `loginController.php` is updated to use `password_verify()`. Until then, log in with a plaintext-hash user OR temporarily set the delivery user's `password_hash` to `delivery123` plaintext:
  ```sql
  UPDATE users SET password_hash='delivery123' WHERE email='delivery@shophub.com';
  ```
- Login entry: <http://localhost/ecommerce/public/?c=auth&a=login>
- Logout: <http://localhost/ecommerce/public/?c=auth&a=logout>
