import sys
from PyPDF2 import PdfReader, PdfWriter

def compress_pdf(input_file, output_file):
    try:
        reader = PdfReader(input_file)
        writer = PdfWriter()

        for page in reader.pages:
            page.compress_content_streams()
            writer.add_page(page)

        with open(output_file, "wb") as output_pdf:
            writer.write(output_pdf)

        print("success")

    except Exception as e:
        print(f"Error: {e}", file=sys.stderr)
        sys.exit(1)

if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("Usage: python compress_pdf.py <input_file> <output_file>", file=sys.stderr)
        sys.exit(1)

    input_file = sys.argv[1]
    output_file = sys.argv[2]
    compress_pdf(input_file, output_file)
