from flask import Flask, request, jsonify
from flask_cors import CORS
import base64, re, asyncio

app = Flask(__name__)
CORS(app)

def clean_html_for_tts(html_text: str) -> str:
    text = re.sub(r'<[^>]+>', ' ', html_text or '')
    text = re.sub(r'\s+', ' ', text)
    return text.strip()

def clean_text_for_tts(text: str) -> str:
    if not text:
        return ""
    if '<' in text and '>' in text:
        text = clean_html_for_tts(text)
    text = re.sub(r'<[^>]+>', '', text)
    text = re.sub(r'#{1,6}\s*', '', text)
    text = re.sub(r'\*{1,3}([^\*]+)\*{1,3}', r'\1', text)
    text = re.sub(r'_{1,3}([^_]+)_{1,3}', r'\1', text)
    text = re.sub(r'`{1,3}[^`]*`{1,3}', '', text)
    text = re.sub(r'\[([^\]]+)\]\([^\)]+\)', r'\1', text)
    text = re.sub(r'^[\s]*[-\*\+•▪▫◦‣⁃]\s+', '', text, flags=re.MULTILINE)
    text = re.sub(r'^[\s]*\d+[\.\)]\s+', '', text, flags=re.MULTILINE)
    text = re.sub(r'^[\s]*[a-zA-Z][\.\)]\s+', '', text, flags=re.MULTILINE)
    text = re.sub(r'[\U00010000-\U0010ffff]', '', text)
    text = re.sub(r'[\u2600-\u26FF\u2700-\u27BF]', '', text)
    text = re.sub(r'[\x00-\x1F\x7F-\x9F]', '', text)
    text = re.sub(r'[\u200B-\u200D\uFEFF]', '', text)
    text = re.sub(r'[\u2000-\u200F]', ' ', text)
    mapping = {'ك': 'ک', 'ي': 'ی', 'ى': 'ی', 'ة': 'ه', 'ؤ': 'و', 'إ': 'ا', 'أ': 'ا', 'ء': '', 'ئ': 'ی'}
    for a, p in mapping.items():
        text = text.replace(a, p)
    text = re.sub(r'\n\n+', '.   ', text)
    text = re.sub(r'\n+', '.  ', text)
    text = re.sub(r'،\s*', '،  ', text)
    text = re.sub(r',\s*', '،  ', text)
    text = re.sub(r'؛\s*', '؛   ', text)
    text = re.sub(r';\s*', '؛   ', text)
    text = re.sub(r':\s*', ':  ', text)
    text = re.sub(r'\.\s*', '.   ', text)
    text = re.sub(r'؟\s*', '?   ', text)
    text = re.sub(r'\?\s*', '?   ', text)
    text = re.sub(r'!\s*', '.   ', text)
    text = re.sub(r'\s+', ' ', text).strip()
    return text

def split_text_for_tts(text: str, max_length: int = 400):
    text = clean_text_for_tts(text)
    if len(text) <= max_length:
        return [text]
    chunks, current = [], ""
    paragraphs = re.split(r'\n\n+|\n+', text)
    for paragraph in paragraphs:
        if len(paragraph) <= max_length:
            if current and len(current) + len(paragraph) + 2 <= max_length:
                current += " . " + paragraph
            else:
                if current:
                    chunks.append(current.strip())
                current = paragraph
        else:
            if current:
                chunks.append(current.strip())
                current = ""
            sentences = re.split(r'(?<=[.!?؟])\s+', paragraph)
            for s in sentences:
                if len(s) <= max_length:
                    if current and len(current) + len(s) + 1 <= max_length:
                        current += " " + s
                    else:
                        if current:
                            chunks.append(current.strip())
                        current = s
                else:
                    if current:
                        chunks.append(current.strip())
                        current = ""
                    parts = re.split(r'[،,؛;]\s*', s)
                    for i, part in enumerate(parts):
                        if i > 0:
                            part = "، " + part
                        if len(current) + len(part) <= max_length:
                            current += part
                        else:
                            if current:
                                chunks.append(current.strip())
                            current = part
    if current:
        chunks.append(current.strip())
    return [c for c in chunks if c]

async def _edge_tts_bytes(
    text: str,
    voice: str = "fa-IR-DilaraNeural",
    rate: str = "+0%",
    volume: str = "+0%",
    pitch: str = "+0Hz",
) -> bytes:
    import edge_tts
    communicate = edge_tts.Communicate(
        text=text, voice=voice, rate=rate, volume=volume, pitch=pitch
    )
    audio = b""
    async for chunk in communicate.stream():
        if chunk["type"] == "audio":
            audio += chunk["data"]
    return audio

def synthesize_with_edge_tts(
    text: str,
    voice: str = None,
    rate: str = None,
    volume: str = None,
    pitch: str = None,
) -> bytes:
    voices_try = []
    if voice:
        voices_try.append(voice)
    voices_try += ["fa-IR-DilaraNeural", "fa-IR-FaridNeural"]
    last_err = None
    for v in voices_try:
        try:
            return asyncio.run(_edge_tts_bytes(
                text=text,
                voice=v,
                rate=rate or "+0%",
                volume=volume or "+0%",
                pitch=pitch or "+0Hz",
            ))
        except Exception as e:
            last_err = e
            continue
    try:
        return asyncio.run(_edge_tts_bytes(
            text=text, voice="ar-EG-SalmaNeural", rate=rate or "+0%", volume=volume or "+0%", pitch=pitch or "+0Hz",
        ))
    except Exception as e:
        raise last_err or e

@app.route('/api/text-to-speech', methods=['POST'])
def text_to_speech_free():
    try:
        data = request.get_json(silent=True) or {}
        text = (data.get('text') or '').strip()
        chunk_index = int(data.get('chunk_index', 0))
        voice = (data.get('voice') or '').strip() or None
        rate = (data.get('rate') or '').strip() or None
        volume = (data.get('volume') or '').strip() or None
        pitch = (data.get('pitch') or '').strip() or None
        if not text:
            return jsonify({"success": False, "error": "Empty text"}), 400
        text = clean_text_for_tts(text)
        text = text.translate(str.maketrans("0123456789", "۰۱۲۳۴۵۶۷۸۹"))
        if len(text) > 1000:
            text = text[:997] + '...'
        audio_data = synthesize_with_edge_tts(text, voice=voice, rate=rate, volume=volume, pitch=pitch)
        if not audio_data:
            return jsonify({"success": False, "error": "No audio generated"}), 500
        b64 = base64.b64encode(audio_data).decode('utf-8')
        return jsonify({
            "success": True,
            "audio": f"data:audio/mp3;base64,{b64}",
            "chunk_index": chunk_index
        })
    except Exception as e:
        return jsonify({"success": False, "error": "Error generating audio"}), 500

@app.route('/api/text-to-speech/chunks', methods=['POST'])
def text_chunks_free():
    try:
        data = request.get_json(silent=True) or {}
        text = (data.get('text') or '').strip()
        if not text:
            return jsonify({"success": False, "error": "Empty text"}), 400
        text = clean_text_for_tts(text)
        chunks = split_text_for_tts(text, max_length=400)
        return jsonify({"success": True, "chunks": chunks, "total": len(chunks)})
    except Exception:
        return jsonify({"success": False, "error": "Error processing text"}), 500

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000, debug=True)
