import sys
from PyPDF2 import PdfReader, PdfWriter

def rotate_pdf(input_file, output_file, rotation):

    reader = PdfReader(input_file)
    writer = PdfWriter()
    for page in reader.pages:
        page.rotate(rotation)
        writer.add_page(page)
    with open(output_file, "wb") as output_pdf:
        writer.write(output_pdf)
    print("success")

if __name__ == "__main__":
    input_file = sys.argv[1]
    output_file = sys.argv[2]
    rotation = int(sys.argv[3])
    rotate_pdf(input_file, output_file, rotation)