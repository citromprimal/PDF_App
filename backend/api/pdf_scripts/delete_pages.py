import sys
from PyPDF2 import PdfReader, PdfWriter

def delete_pages(input_file, output_file, pages):

    try:
        reader = PdfReader(input_file)
        writer = PdfWriter()
        pages_to_delete = parse_pages(pages, len(reader.pages))
        for i, page in enumerate(reader.pages):
            if i + 1 not in pages_to_delete:
                writer.add_page(page)
        with open(output_file, "wb") as f:
            writer.write(f)
        print("success")
    except Exception as e:
        print(str(e))

def parse_pages(pages_string, total_pages):
    """Parses a string of page numbers and ranges into a list of integers."""
    page_numbers = set()
    parts = pages_string.split(',')
    for part in parts:
        if '-' in part:
            start, end = map(int, part.split('-'))
            for i in range(start, end + 1):
                if 1 <= i <= total_pages:
                    page_numbers.add(i)
        else:
            page_number = int(part)
            if 1 <= page_number <= total_pages:
                page_numbers.add(page_number)
    return sorted(list(page_numbers))

if __name__ == "__main__":
    input_file = sys.argv[1]
    output_file = sys.argv[2]
    pages = sys.argv[3]
    delete_pages(input_file, output_file, pages)
