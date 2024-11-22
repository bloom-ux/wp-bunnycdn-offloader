# CDN Offloader

A simple plugin to upload and serve your attachment files from Bunny CDN.

## Installation

Install with composer: `composer require bloom-ux/wp-bunnycdn-offloader`

Define the following environment variables:

```
# The secret API key used as password to upload files to your storage
BLOOM_BUNNY_STORAGE_API_KEY=""

# The storage zone name
BLOOM_BUNNY_STORAGE_ZONE=""

# Two-letter code of the storage region (ie: "br")
BLOOM_BUNNY_STORAGE_REGION=""

# Full URL to your assets (it can include a folder). ie: "https://my-storage.b-cdn.net/subfolder/"
BLOOM_BUNNY_PUBLIC_URL=""
```
