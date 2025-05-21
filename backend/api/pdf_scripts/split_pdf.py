import sys
from PyPDF2 import PdfReader, PdfWriter
import os
import zipfile
import tempfile

def split_pdf_into_two(input_path, split_page_number, output_zip_path):
    try:
        reader = PdfReader(input_path)
        total_pages = len(reader.pages)

        if not isinstance(split_page_number, int) or split_page_number < 1 or split_page_number >= total_pages:
            raise ValueError(f"Invalid split page number: {split_page_number}. Must be between 1 and {total_pages - 1}.")

        with tempfile.NamedTemporaryFile(suffix="_part1.pdf", delete=False) as temp_part1:
            part1_filename = temp_part1.name
        with tempfile.NamedTemporaryFile(suffix="_part2.pdf", delete=False) as temp_part2:
            part2_filename = temp_part2.name

        writer1 = PdfWriter()
        for i in range(split_page_number):
            writer1.add_page(reader.pages[i])
        with open(part1_filename, 'wb') as f1:
            writer1.write(f1)

        writer2 = PdfWriter()
        for i in range(split_page_number, total_pages):
            writer2.add_page(reader.pages[i])
        with open(part2_filename, 'wb') as f2:
            writer2.write(f2)

        with zipfile.ZipFile(output_zip_path, 'w', zipfile.ZIP_DEFLATED) as zipf:

            zipf.write(part1_filename, arcname=f"part1_pages1-{split_page_number}.pdf")
            zipf.write(part2_filename, arcname=f"part2_pages{split_page_number + 1}-{total_pages}.pdf")

        os.remove(part1_filename)
        os.remove(part2_filename)

        print("success")

    except Exception as e:
        if 'part1_filename' in locals() and os.path.exists(part1_filename):
             try: os.remove(part1_filename)
             except Exception as cleanup_e: print(f"Error cleaning up {part1_filename}: {cleanup_e}", file=sys.stderr)
        if 'part2_filename' in locals() and os.path.exists(part2_filename):
             try: os.remove(part2_filename)
             except Exception as cleanup_e: print(f"Error cleaning up {part2_filename}: {cleanup_e}", file=sys.stderr)

        print(f"Error: {e}", file=sys.stderr)
        sys.exit(1)

if __name__ == "__main__":
    if len(sys.argv) != 4:
        print("Usage: python split_pdf.py <input_pdf_path> <split_page_number> <output_zip_path>", file=sys.stderr)
        sys.exit(1)

    input_pdf = sys.argv[1]
    try:
        split_page = int(sys.argv[2])
    except ValueError:
        print("Error: split_page_number must be an integer.", file=sys.stderr)
        sys.exit(1)
    output_zip = sys.argv[3]

    split_pdf_into_two(input_pdf, split_page, output_zip)