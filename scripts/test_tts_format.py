#!/usr/bin/env python3
import edge_tts
import asyncio

async def test():
    c = edge_tts.Communicate('test', 'fa-IR-DilaraNeural')
    async for chunk in c.stream():
        if chunk['type'] == 'audio':
            print(f"Audio chunk size: {len(chunk['data'])}")
            # Check first few bytes to detect format
            first_bytes = chunk['data'][:20]
            print(f"First bytes (hex): {first_bytes.hex()}")
            # WebM starts with: 1a 45 df a3
            if first_bytes.startswith(b'\x1a\x45\xdf\xa3'):
                print("Format: WebM")
            # MP3 starts with: FF FB or FF F3 or ID3
            elif first_bytes.startswith(b'\xff\xfb') or first_bytes.startswith(b'\xff\xf3') or first_bytes.startswith(b'ID3'):
                print("Format: MP3")
            else:
                print("Format: Unknown")
            break

asyncio.run(test())

