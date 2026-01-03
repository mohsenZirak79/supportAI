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


def clean_text_for_tts(text, lang_code='fa'):
    """Clean text completely - backward compatible wrapper for clean_text_for_tts_by_lang"""
    return clean_text_for_tts_by_lang(text, lang_code)


def split_text_for_tts(text, max_length=350, lang_code='fa'):
    """Split text into smaller chunks for faster TTS processing with natural breaks"""
    # Clean text first to add natural pauses, then split
    text = clean_text_for_tts_by_lang(text, lang_code)
    
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


def get_language_config(lang_code, gender='female'):
    """
    Get language configuration by code and gender.
    Returns Persian female voice if not found.
    
    Args:
        lang_code: Language code ('fa', 'en', 'ar')
        gender: Voice gender ('male' or 'female')
    """
    lang_code = (lang_code or 'fa').lower().strip()
    gender = (gender or 'female').lower().strip()
    
    # Voice configurations for each language and gender
    SUPPORTED_LANGUAGES = {
        "fa": {
            "name": "فارسی",
            "name_en": "Persian",
            "voices": {
                "female": {
                    "primary": "fa-IR-DilaraNeural",
                    "fallback": "fa-IR-DilaraNeural"
                },
                "male": {
                    "primary": "fa-IR-FaridNeural",
                    "fallback": "fa-IR-FaridNeural"
                }
            }
        },
        "en": {
            "name": "English",
            "name_en": "English",
            "voices": {
                "female": {
                    "primary": "en-US-JennyNeural",
                    "fallback": "en-US-AriaNeural"
                },
                "male": {
                    "primary": "en-US-GuyNeural",
                    "fallback": "en-US-ChristopherNeural"
                }
            }
        },
        "ar": {
            "name": "العربية",
            "name_en": "Arabic",
            "voices": {
                "female": {
                    "primary": "ar-SA-ZariyahNeural",
                    "fallback": "ar-EG-SalmaNeural"
                },
                "male": {
                    "primary": "ar-SA-HamedNeural",
                    "fallback": "ar-EG-ShakirNeural"
                }
            }
        }
    }
    
    lang_config = SUPPORTED_LANGUAGES.get(lang_code, SUPPORTED_LANGUAGES["fa"])
    voice_config = lang_config["voices"].get(gender, lang_config["voices"]["female"])
    
    return {
        "name": lang_config["name"],
        "name_en": lang_config["name_en"],
        "tts_voice": voice_config["primary"],
        "tts_voice_fallback": voice_config["fallback"],
        "gender": gender
    }


def clean_text_for_tts_by_lang(text, lang_code='fa'):
    """Clean text for TTS based on language"""
    lang_code = (lang_code or 'fa').lower().strip()
    
    # If text contains HTML, clean it first
    if '<' in text and '>' in text:
        text = clean_html_for_tts(text)
    
    # Remove HTML/XML tags
    text = re.sub(r'<[^>]+>', '', text)
    
    # Remove markdown formatting
    text = re.sub(r'#{1,6}\s*', '', text)
    text = re.sub(r'\*{1,3}([^\*]+)\*{1,3}', r'\1', text)
    text = re.sub(r'_{1,3}([^_]+)_{1,3}', r'\1', text)
    text = re.sub(r'`{1,3}[^`]*`{1,3}', '', text)
    text = re.sub(r'\[([^\]]+)\]\([^\)]+\)', r'\1', text)
    
    # Remove bullet points and numbers
    text = re.sub(r'^[\s]*[-\*\+•▪▫◦‣⁃]\s+', '', text, flags=re.MULTILINE)
    text = re.sub(r'^[\s]*\d+[\.\)]\s+', '', text, flags=re.MULTILINE)
    text = re.sub(r'^[\s]*[a-zA-Z][\.\)]\s+', '', text, flags=re.MULTILINE)
    
    # Remove emoji and special characters
    text = re.sub(r'[\U00010000-\U0010ffff]', '', text)
    text = re.sub(r'[\u2600-\u26FF\u2700-\u27BF]', '', text)
    
    # Remove control characters
    text = re.sub(r'[\x00-\x1F\x7F-\x9F]', '', text)
    text = re.sub(r'[\u200B-\u200D\uFEFF]', '', text)
    text = re.sub(r'[\u2000-\u200F]', ' ', text)
    
    # Language-specific cleaning
    if lang_code == 'fa':
        # Persian-specific cleaning
        text = re.sub(r'\u200c', '', text)  # ZWNJ
        
        # Normalize Arabic to Persian
        arabic_to_persian = {
            'ك': 'ک', 'ي': 'ی', 'ى': 'ی', 'ة': 'ه',
            'ؤ': 'و', 'إ': 'ا', 'أ': 'ا', 'ء': '', 'ئ': 'ی'
        }
        for arabic, persian in arabic_to_persian.items():
            text = text.replace(arabic, persian)
        
        # Replace English punctuation with Persian equivalents
        text = text.replace(',', '،')
        text = text.replace('?', '؟')
        text = text.replace('!', '.')
        text = text.replace(';', '،')
        text = text.replace(':', '،')
        
        # Keep only Persian characters, numbers, spaces, and basic punctuation
        text = re.sub(r'[^\u0600-\u06FF\u06F0-\u06F9\s\.،؟]', '', text)
        
        # Convert English numbers to Persian numbers
        persian_numbers = '۰۱۲۳۴۵۶۷۸۹'
        english_numbers = '0123456789'
        trans_table = str.maketrans(english_numbers, persian_numbers)
        text = text.translate(trans_table)
    elif lang_code == 'ar':
        # Arabic-specific cleaning
        text = re.sub(r'\u200c', '', text)  # ZWNJ
        
        # Replace English punctuation (keep Arabic punctuation)
        text = text.replace('?', '؟')
        text = text.replace(',', '،')
        
        # Convert English numbers to Arabic-Indic numbers
        arabic_numbers = '٠١٢٣٤٥٦٧٨٩'
        english_numbers = '0123456789'
        trans_table = str.maketrans(english_numbers, arabic_numbers)
        text = text.translate(trans_table)
    # For English, keep as-is (just basic cleanup)
    
    # Normalize multiple spaces to single space
    text = re.sub(r'\s+', ' ', text)
    
    # Clean up punctuation spacing
    if lang_code == 'fa':
        text = re.sub(r'([\.،؟])\s*', r'\1 ', text)
        text = re.sub(r'\s+([\.،؟])', r'\1', text)
    elif lang_code == 'ar':
        text = re.sub(r'([\.،؟])\s*', r'\1 ', text)
        text = re.sub(r'\s+([\.،؟])', r'\1', text)
    else:  # English
        text = re.sub(r'([\.!?])\s*', r'\1 ', text)
        text = re.sub(r'\s+([\.!?])', r'\1', text)
    
    # Remove multiple consecutive punctuation marks
    if lang_code == 'fa':
        text = re.sub(r'([\.،؟]){2,}', r'\1', text)
    elif lang_code == 'ar':
        text = re.sub(r'([\.،؟]){2,}', r'\1', text)
    else:
        text = re.sub(r'([\.!?]){2,}', r'\1', text)
    
    # Normalize line breaks to spaces
    text = re.sub(r'\n+', ' ', text)
    
    # Final cleanup
    text = re.sub(r'\s+', ' ', text)
    text = text.strip()
    
    return text


def main():
    """Main function to handle TTS request - matches reference app.py exactly"""
    try:
        # Read input from stdin (JSON)
        input_data = json.loads(sys.stdin.read())
        text = input_data.get('text', '')
        chunk_index = input_data.get('chunk_index', 0)
        lang_code = input_data.get('lang', 'fa')  # Default to Persian
        gender = input_data.get('gender', 'female')  # Default to female voice
        
        if not text:
            print(json.dumps({
                "success": False,
                "error": "متن خالی است"
            }))
            sys.exit(1)
        
        # Get language configuration with gender preference
        lang_config = get_language_config(lang_code, gender)
        
        # Clean the text based on language
        text = clean_text_for_tts_by_lang(text, lang_code)
        
        if not text or not text.strip():
            print(json.dumps({
                "success": False,
                "error": "متن پس از پاکسازی خالی است"
            }))
            sys.exit(1)
        
        # Limit text length for better performance (Edge TTS handles long text well)
        if len(text) > 5000:
            text = text[:4997] + '...'
        
        # Use Microsoft Edge TTS (FREE, no API key required)
        try:
            async def generate_speech():
                # Use language-specific voices
                voices = [
                    (lang_config["tts_voice"], "primary"),
                    (lang_config["tts_voice_fallback"], "fallback"),
                ]
                
                for voice_name, voice_type in voices:
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
                        
                        if audio_data and len(audio_data) > 0:
                            return audio_data
                    except Exception as e:
                        continue
                
                return None
            
            # Run async function - exactly like reference app.py
            loop = asyncio.new_event_loop()
            asyncio.set_event_loop(loop)
            try:
                audio_data = loop.run_until_complete(generate_speech())
            finally:
                loop.close()
            
            if audio_data and len(audio_data) > 0:
                audio_base64 = base64.b64encode(audio_data).decode('utf-8')
                # Edge TTS returns WebM format (OPUS codec), not MP3
                print(json.dumps({
                    "success": True,
                    "audio": f"data:audio/webm;base64,{audio_base64}",
                    "chunk_index": chunk_index,
                    "lang": lang_code,
                    "gender": gender
                }))
            else:
                print(json.dumps({
                    "success": False,
                    "error": "تولید صوت ناموفق بود"
                }))
                sys.exit(1)
                
        except ImportError:
            print(json.dumps({
                "success": False,
                "error": "کتابخانه edge-tts نصب نشده است. لطفا با دستور 'pip install edge-tts' نصب کنید"
            }))
            sys.exit(1)
        except Exception as edge_error:
            print(json.dumps({
                "success": False,
                "error": f"خطا در تولید صوت: {str(edge_error)}"
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
            "error": "خطا در پردازش درخواست"
        }))
        sys.exit(1)


if __name__ == '__main__':
    # main() is now synchronous and handles its own event loop internally
    main()

