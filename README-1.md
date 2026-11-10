# CHANGELOG

## [Day 9] - 2025-11-04
### Added
- User model scope: `scopeWhereEmail`
- `Auth\PasswordController`
- Form Requests: `ForgotPasswordRequest`, `PasswordResetRequest`, `UnlockService`, `ProfileService`
- Services: `ForgotPasswordService`, `ResetPasswordService`, `UnlockRequest`

### Changed
- OTP folder restructured to `app/Services/Auth/Otp/`
  - `BaseOtpService.php`
  - `LoginOtpService.php`
  - `RegisterOtpService.php`

