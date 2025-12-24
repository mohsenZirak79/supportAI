#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Split text into chunks for streaming TTS
Matches the implementation from the reference app.py
"""

import sys
import json
import re


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


def split_text_for_tts(text, max_length=350):
    """Split text into smaller chunks for faster TTS processing with natural breaks"""
    # Clean text first to add natural pauses, then split
    text = clean_text_for_tts(text)
    
    if len(text) <= max_length:
        return [text]
    
    chunks = []
    
    # First split by paragraphs (double newline or single newline)
    paragraphs = re.split(r'\n\n+|\n+', text)
    
    current_chunk = ""
    
    for paragraph in paragraphs:
        # If paragraph is short enough, try to add it to current chunk
        if len(paragraph) <= max_length:
            # Check if we can add this paragraph to current chunk
            if current_chunk and len(current_chunk) + len(paragraph) + 2 <= max_length:
                # Add with proper separator for pause
                current_chunk += " . " + paragraph
            else:
                # Save current chunk if exists
                if current_chunk:
                    chunks.append(current_chunk.strip())
                current_chunk = paragraph
        else:
            # Paragraph is too long, need to split it
            # First save any existing chunk
            if current_chunk:
                chunks.append(current_chunk.strip())
                current_chunk = ""
            
            # Split long paragraph by sentences
            sentences = re.split(r'(?<=[.!?؟])\s+', paragraph)
            
            for sentence in sentences:
                if len(sentence) <= max_length:
                    if current_chunk and len(current_chunk) + len(sentence) + 1 <= max_length:
                        current_chunk += " " + sentence
                    else:
                        if current_chunk:
                            chunks.append(current_chunk.strip())
                        current_chunk = sentence
                else:
                    # Even sentence is too long, split by commas
                    if current_chunk:
                        chunks.append(current_chunk.strip())
                        current_chunk = ""
                    
                    # Split by comma or semicolon
                    parts = re.split(r'[،,؛;]\s*', sentence)
                    for i, part in enumerate(parts):
                        if i > 0:
                            # Add comma back for natural pause
                            part = "، " + part
                        
                        if len(current_chunk) + len(part) <= max_length:
                            current_chunk += part
                        else:
                            if current_chunk:
                                chunks.append(current_chunk.strip())
                            current_chunk = part
    
    # Don't forget the last chunk
    if current_chunk:
        chunks.append(current_chunk.strip())
    
    # Clean each chunk
    processed_chunks = []
    for chunk in chunks:
        chunk = chunk.strip()
        if chunk:  # Only add non-empty chunks
            processed_chunks.append(chunk)
    
    return processed_chunks


def main():
    """Main function to handle chunks request - matches reference app.py exactly"""
    try:
        data = json.loads(sys.stdin.read())
        text = data.get('text', '')
        
        if not text:
            print(json.dumps({
                "success": False,
                "error": "متن خالی است"
            }))
            sys.exit(1)
        
        # Clean the text first
        text = clean_text_for_tts(text)
        
        # Split into chunks
        chunks = split_text_for_tts(text, max_length=400)
        
        print(json.dumps({
            "success": True,
            "chunks": chunks,
            "total": len(chunks)
        }))
        
    except json.JSONDecodeError:
        print(json.dumps({
            "success": False,
            "error": "خطا در پردازش JSON ورودی"
        }))
        sys.exit(1)
    except Exception as e:
        print(json.dumps({
            "success": False,
            "error": "خطا در پردازش متن"
        }))
        sys.exit(1)


if __name__ == '__main__':
    main()

