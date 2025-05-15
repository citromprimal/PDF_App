import sys
from PyPDF2 import PdfReader, PdfWriter

def compress_pdf(input_file, output_file):

    reader = PdfReader(input_file)
    writer = PdfWriter()
    for page in reader.pages:
        writer.add_page(page)
    writer.write(output_file)
    print("success")

if __name__ == "__main__":
    input_file = sys.argv[1]
    output_file = sys.argv[2]
    compress_pdf(input_file, output_file)
