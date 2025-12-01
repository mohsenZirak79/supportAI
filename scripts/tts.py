#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Text-to-Speech using Microsoft Edge TTS (FREE) with Persian support
Matches the implementation from the reference app.py
"""

import sys
import json
import re
import base64
import asyncio

try:
    import edge_tts
except ImportError:
    print(json.dumps({
        "success": False,
        "error": "کتابخانه edge-tts نصب نشده است. لطفا با دستور 'pip install edge-tts' نصب کنید"
    }), file=sys.stderr)
    sys.exit(1)


def clean_html_for_tts(html_text):
    """Extract plain text from HTML for TTS"""
    text = re.sub(r'<[^>]+>', ' ', html_text)
    text = re.sub(r'\s+', ' ', text)
    return text.strip()


def clean_text_for_tts(text):
    """Clean text completely - remove ALL extra symbols, keep only Persian text and basic punctuation"""
    # If text contains HTML, clean it first
    if '<' in text and '>' in text:
        text = clean_html_for_tts(text)
    
    # حذف تمام تگ‌های HTML/XML
    text = re.sub(r'<[^>]+>', '', text)
    
    # حذف markdown formatting
    text = re.sub(r'#{1,6}\s*', '', text)  # حذف headers
    text = re.sub(r'\*{1,3}([^\*]+)\*{1,3}', r'\1', text)  # حذف bold/italic
    text = re.sub(r'_{1,3}([^_]+)_{1,3}', r'\1', text)  # حذف underline
    text = re.sub(r'`{1,3}[^`]*`{1,3}', '', text)  # حذف code blocks
    text = re.sub(r'\[([^\]]+)\]\([^\)]+\)', r'\1', text)  # حذف links
    
    # حذف تمام bullet points و شماره‌ها
    text = re.sub(r'^[\s]*[-\*\+•▪▫◦‣⁃]\s+', '', text, flags=re.MULTILINE)
    text = re.sub(r'^[\s]*\d+[\.\)]\s+', '', text, flags=re.MULTILINE)
    text = re.sub(r'^[\s]*[a-zA-Z][\.\)]\s+', '', text, flags=re.MULTILINE)
    
    # حذف emoji و کاراکترهای خاص
    text = re.sub(r'[\U00010000-\U0010ffff]', '', text)  # حذف emoji
    text = re.sub(r'[\u2600-\u26FF\u2700-\u27BF]', '', text)  # حذف symbols
    
    # حذف کاراکترهای نامرئی و کنترلی
    text = re.sub(r'[\x00-\x1F\x7F-\x9F]', '', text)  # control characters
    text = re.sub(r'[\u200B-\u200D\uFEFF]', '', text)  # zero-width characters
    text = re.sub(r'[\u2000-\u200F]', ' ', text)  # various spaces
    
    # حذف نیم‌فاصله (ZWNJ)
    text = re.sub(r'\u200c', '', text)
    
    # نرمالیزه کردن نویسه‌های عربی به فارسی
    arabic_to_persian = {
        'ك': 'ک',
        'ي': 'ی', 
        'ى': 'ی',
        'ة': 'ه',
        'ؤ': 'و',
        'إ': 'ا',
        'أ': 'ا',
        'ء': '',
        'ئ': 'ی'
    }
    for arabic, persian in arabic_to_persian.items():
        text = text.replace(arabic, persian)
    
    # حذف تمام کاراکترهای غیر ضروری - فقط نگه داشتن فارسی، اعداد، و علائم نگارشی اصلی
    # First, replace English punctuation with Persian equivalents
    text = text.replace(',', '،')  # English comma to Persian comma
    text = text.replace('?', '؟')  # English question mark to Persian
    text = text.replace('!', '.')  # Exclamation to period
    text = text.replace(';', '،')  # Semicolon to Persian comma
    text = text.replace(':', '،')  # Colon to Persian comma
    
    # Remove ALL other symbols except Persian letters, numbers, spaces, and basic punctuation
    # Keep: Persian letters (\u0600-\u06FF), Persian numbers (\u06F0-\u06F9), space, period (.), Persian comma (،), Persian question mark (؟)
    allowed_chars_pattern = r'[^\u0600-\u06FF\u06F0-\u06F9\s\.،؟]'
    text = re.sub(allowed_chars_pattern, '', text)
    
    # Normalize multiple spaces to single space
    text = re.sub(r'\s+', ' ', text)
    
    # Clean up punctuation spacing - ensure single space after punctuation
    text = re.sub(r'([\.،؟])\s*', r'\1 ', text)  # Single space after punctuation
    text = re.sub(r'\s+([\.،؟])', r'\1', text)  # Remove space before punctuation
    
    # Remove multiple consecutive punctuation marks
    text = re.sub(r'([\.،؟]){2,}', r'\1', text)
    
    # Normalize line breaks to spaces
    text = re.sub(r'\n+', ' ', text)
    
    # Final cleanup - remove extra spaces
    text = re.sub(r'\s+', ' ', text)
    text = text.strip()
    
    return text


async def generate_speech(text, voice_name):
    """Generate speech using edge_tts"""
    try:
        communicate = edge_tts.Communicate(
            text=text,
            voice=voice_name,
            rate="+0%",      # Normal speed
            volume="+0%",    # Normal volume
            pitch="+0Hz"     # Normal pitch
        )
        
        audio_data = b""
        async for chunk in communicate.stream():
            if chunk["type"] == "audio":
                audio_data += chunk["data"]
        
        return audio_data if audio_data and len(audio_data) > 0 else None
    except Exception:
        return None


async def main():
    """Main function to handle TTS request"""
    try:
        # Read input from stdin (JSON)
        input_data = json.loads(sys.stdin.read())
        text = input_data.get('text', '')
        chunk_index = input_data.get('chunk_index', 0)
        
        if not text:
            print(json.dumps({
                "success": False,
                "error": "متن خالی است"
            }))
            sys.exit(1)
        
        # Clean the text completely - remove ALL extra symbols
        text = clean_text_for_tts(text)
        
        if not text or not text.strip():
            print(json.dumps({
                "success": False,
                "error": "متن پس از پاکسازی خالی است"
            }))
            sys.exit(1)
        
        # Convert English numbers to Persian numbers for better pronunciation
        persian_numbers = '۰۱۲۳۴۵۶۷۸۹'
        english_numbers = '0123456789'
        trans_table = str.maketrans(english_numbers, persian_numbers)
        text = text.translate(trans_table)
        
        # Limit text length for better performance (Edge TTS handles long text well)
        if len(text) > 5000:
            text = text[:4997] + '...'
        
        # Best Persian voices from Microsoft Edge TTS
        voices = [
            ("fa-IR-DilaraNeural", "female"),  # Primary: Female Persian voice
            ("fa-IR-FaridNeural", "male"),      # Fallback: Male Persian voice
        ]
        
        audio_data = None
        for voice_name, voice_type in voices:
            audio_data = await generate_speech(text, voice_name)
            if audio_data:
                break
        
        if audio_data and len(audio_data) > 0:
            audio_base64 = base64.b64encode(audio_data).decode('utf-8')
            print(json.dumps({
                "success": True,
                "audio": f"data:audio/mp3;base64,{audio_base64}",
                "chunk_index": chunk_index
            }))
        else:
            print(json.dumps({
                "success": False,
                "error": "تولید صوت ناموفق بود"
            }))
            sys.exit(1)
            
    except json.JSONDecodeError:
        print(json.dumps({
            "success": False,
            "error": "خطا در پردازش JSON ورودی"
        }))
        sys.exit(1)
    except Exception as e:
        print(json.dumps({
            "success": False,
            "error": f"خطا در تولید صوت: {str(e)}"
        }))
        sys.exit(1)


if __name__ == '__main__':
    asyncio.run(main())

