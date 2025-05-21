import sys
from reportlab.pdfgen import canvas
from reportlab.lib.units import inch
from PyPDF2 import PdfReader, PdfWriter
from PyPDF2 import PdfFileWriter
import os
import tempfile

def add_watermark_to_pdf(input_pdf_path, watermark_text, output_pdf_path):
    try:
        reader = PdfReader(input_pdf_path)
        writer = PdfWriter()

        with tempfile.NamedTemporaryFile(suffix=".pdf", delete=False) as temp_watermark_file:
            watermark_filename = temp_watermark_file.name

        c = canvas.Canvas(watermark_filename)
        c.setFont("Helvetica", 40)
        c.setFillAlpha(0.3)
        c.drawCentredString(4 * inch, 5.5 * inch, watermark_text)
        c.save()

        watermark_reader = PdfReader(watermark_filename)
        watermark_page = watermark_reader.pages[0]

        for i in range(len(reader.pages)):
            original_page = reader.pages[i]
            temp_writer = PdfFileWriter()
            temp_writer.add_page(watermark_reader.pages[0])
            temp_watermark_page = temp_writer.pages[0]

            original_page.merge_page(temp_watermark_page)
            writer.add_page(original_page)


        with open(output_pdf_path, 'wb') as output_file:
            writer.write(output_file)


        os.remove(watermark_filename)

        print("success")

    except Exception as e:
        print(f"Error: {e}", file=sys.stderr)
        if 'watermark_filename' in locals() and os.path.exists(watermark_filename):
             try:
                 os.remove(watermark_filename)
             except Exception as cleanup_e:
                 print(f"Error cleaning up temp file {watermark_filename}: {cleanup_e}", file=sys.stderr)

        sys.exit(1)

if __name__ == "__main__":
    if len(sys.argv) != 4:
        print("Usage: python add_watermark.py <input_pdf_path> <watermark_text> <output_pdf_path>", file=sys.stderr)
        sys.exit(1)

    input_pdf = sys.argv[1]
    watermark_text = sys.argv[2]
    output_pdf = sys.argv[3]

    add_watermark_to_pdf(input_pdf, watermark_text, output_pdf)