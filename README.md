# CDN Offloader

A simple plugin to upload and serve your attachment files from Bunny CDN.

## Installation

Install with composer: `composer require bloom-ux/wp-bunnycdn-offloader`

## Configuration

Define the following environment variables:

```env
# The secret API key used as password to upload files to your storage
BLOOM_BUNNY_STORAGE_API_KEY=""

# The storage zone name
BLOOM_BUNNY_STORAGE_ZONE=""

# Two-letter code of the storage region (ie: "br")
BLOOM_BUNNY_STORAGE_REGION=""

# Full URL to your assets (it can include a folder). ie: "https://my-storage.b-cdn.net/subfolder/"
BLOOM_BUNNY_PUBLIC_URL=""
```

## Disable uploads to Bunny

To disable uploads to Bunny (useful in development environments or during imports),
define the constant `BLOOM_BUNNY_OFFLOADER_DISABLE` as `true` in `wp-config.php`:

```php
define( 'BLOOM_BUNNY_OFFLOADER_DISABLE', true );
```

Or use the environment variable in your `.env` file:

```env
BLOOM_BUNNY_OFFLOADER_DISABLE=true
```

When this option is active, the plugin will not register hooks or WP-CLI commands,
so files will not be uploaded to Bunny CDN and will be served from the local server.
