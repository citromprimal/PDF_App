import sys
from PyPDF2 import PdfMerger

def merge_pdfs(input_files, output_file):

    merger = PdfMerger()
    for pdf in input_files:
        merger.append(pdf)
    merger.write(output_file)
    merger.close()
    print("success")

if __name__ == "__main__":
    input_files = sys.argv[1:-1]
    output_file = sys.argv[-1]
    merge_pdfs(input_files, output_file)