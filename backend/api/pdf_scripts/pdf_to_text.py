import sys
from PyPDF2 import PdfReader

def pdf_to_text(input_file_path, output_text_path):

    try:
        reader = PdfReader(input_file_path)

        text = ""
        for page in reader.pages:
            try:
                page_text = page.extract_text()

                if page_text:
                    text += page_text + "\n"
            except Exception as page_e:
                 print(f"Warning: Failed to extract text from a page: {page_e}", file=sys.stderr)

        with open(output_text_path, 'w', encoding='utf-8') as f:
            f.write(text)

        print("success")

    except Exception as e:
        print(f"Error: {e}", file=sys.stderr)
        sys.exit(1)

if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("Usage: python pdf_to_text.py <input_pdf_path> <output_text_path>", file=sys.stderr)
        sys.exit(1)

    input_pdf = sys.argv[1]
    output_text_file = sys.argv[2]

    pdf_to_text(input_pdf, output_text_file)