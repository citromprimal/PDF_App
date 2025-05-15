import sys
from PyPDF2 import PdfReader, PdfWriter

def reorder_pdf_pages(input_path, output_path, order_str):
    try:
        reader = PdfReader(input_path)
        writer = PdfWriter()

        try:
            page_indices_order = [int(p) - 1 for p in order_str.split(',') if p.isdigit()]
        except ValueError:
            raise ValueError("Invalid page number in order string.")

        max_page_index = len(reader.pages) - 1
        if any(idx < 0 or idx > max_page_index for idx in page_indices_order):
             raise ValueError("Page number out of range.")

        for index in page_indices_order:
            writer.add_page(reader.pages[index])

        with open(output_path, 'wb') as output_file:
            writer.write(output_file)

        print("success")
    except Exception as e:
        print(f"Error: {e}", file=sys.stderr)
        sys.exit(1)

if __name__ == "__main__":
    if len(sys.argv) != 4:
        print("Usage: python reorder_pdf.py <input_pdf_path> <output_pdf_path> <comma_separated_order>", file=sys.stderr)
        sys.exit(1)

    input_pdf = sys.argv[1]
    output_pdf = sys.argv[2]
    order_str = sys.argv[3]

    reorder_pdf_pages(input_pdf, output_pdf, order_str)