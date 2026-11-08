# Powermynetworks

| Folder | Site | Hosting |
|---|---|---|
| `powermynet.com/` | Agent shops (customer links) | Hostinger |
| `powermynetworks.com/` | Main system / agent portal (PHP files) | aveshost |

## Not in this repository
Passwords and keys stay on the servers only. These files are excluded (see `.gitignore`) — copy them from the live server when setting up:

- `**/database/dbconfig.php`, `**/waec_dbconfig.php` — database logins
- `**/sms_config.php`, `**/config.php` — SMS / payment settings
- `configs/pmn_provider.php` — Realestplug connection key

Keys that were written directly inside code files are replaced with `REDACTED`. When editing one of those files, take the live copy from the server, not this one.

## Realestplug provider switch
Admin → API Switch → **API Provider** chooses, per product, between the current API (CodeCraft / DataWorks) and Realestplug.
Orders sent to Realestplug use the compatible API at `https://realestplug.com/api/compat/{key}/{codecraft|dataworks}/{endpoint}.php`.
