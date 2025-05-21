import sys
from PIL import Image

def images_to_pdf(image_files, output_file):

    images = []
    for image_file in image_files:
        images.append(Image.open(image_file))
    images[0].save(output_file, "PDF", resolution=100.0, save_all=True, append_images=images[1:])
    print("success")

if __name__ == "__main__":
    image_files = sys.argv[1:-1]
    output_file = sys.argv[-1]
    images_to_pdf(image_files, output_file)