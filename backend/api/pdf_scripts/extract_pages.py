import sys
from PyPDF2 import PdfReader, PdfWriter

def extract_pages(input_file, output_file, pages):

    reader = PdfReader(input_file)
    writer = PdfWriter()
    page_numbers = parse_pages(pages, len(reader.pages))
    for page_num in page_numbers:
        writer.add_page(reader.pages[page_num - 1])
    with open(output_file, "wb") as output_pdf:
        writer.write(output_pdf)
    print("success")

def parse_pages(pages_string, total_pages):

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
    extract_pages(input_file, output_file, pages)