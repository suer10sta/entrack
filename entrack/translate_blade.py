import os
import re
import requests

# LibreTranslate API (free)
API_URL = "https://libretranslate.com/translate"

# Source & Target languages
source_lang = "en"
target_lang = "fr"  # Change to "ar" or others if you want

# Directories
views_dir = "resources/views"
output_dir = f"resources/views/{target_lang}"

os.makedirs(output_dir, exist_ok=True)

def translate_text(text, target_lang="fr"):
    try:
        response = requests.post(
            "https://libretranslate.com/translate",
            json={"q": text, "source": "en", "target": target_lang},
            timeout=10
        )
        if response.status_code == 200:
            return response.json()["translatedText"]
        else:
            return text
    except Exception as e:
        return text

    """Translate text using LibreTranslate API."""
    try:
        response = requests.post(API_URL, data={
            'q': text,
            'source': source_lang,
            'target': target_lang,
            'format': 'text'
        })
        return response.json().get('translatedText', text)
    except Exception:
        return text

def extract_visible_text(html):
    """Find visible sentences outside HTML tags and Blade syntax."""
    # Regex: find visible text not between <...> or {{ ... }}
    return re.findall(r'>([^<>{}\n]+)<', html)

def translate_file(filepath):
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()

    visible_texts = extract_visible_text(content)
    translations = {}

    for text in visible_texts:
        clean_text = text.strip()
        if clean_text:
            translated = translate_text(clean_text)
            translations[clean_text] = translated
            print(f"{clean_text} → {translated}")

    translated_content = content
    for original, translated in translations.items():
        translated_content = translated_content.replace(original, translated)

    output_path = os.path.join(output_dir, os.path.basename(filepath))
    with open(output_path, "w", encoding="utf-8") as f:
        f.write(translated_content)

def process_all_views():
    for root, dirs, files in os.walk(views_dir):
        for file in files:
            if file.endswith(".blade.php"):
                print(f"\nTranslating {file}...")
                translate_file(os.path.join(root, file))

if __name__ == "__main__":
    process_all_views()
    print("\n✅ All visible text translated and saved to:", output_dir)
