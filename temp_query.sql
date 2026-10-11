SELECT id, name, ip_address, stream_url, slug FROM cctvs WHERE stream_url IS NOT NULL AND stream_url = '';
