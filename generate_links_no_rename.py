import os

# Define the directory
dir_path = "FastFinance/files/docs"

# Get the list of PDF files
files = [f for f in os.listdir(dir_path) if f.endswith('.pdf')]

# Sort the files alphabetically
files.sort()

html_links = []

# Generate the HTML links
for filename in files:
    # Sanitize the Russian filename for display
    display_name = filename.split('.pdf')[0]
    if len(display_name) > 2 and display_name[0].isdigit() and (display_name[1] == '.' or display_name[2] == '.'):
        display_name = " ".join(display_name.split(' ')[1:])

    link = f'<a target="_blank" href="/files/docs/{filename}">{display_name}</a><br>'
    html_links.append(link)

# Print the final HTML
print("\n--- Generated HTML ---\n")
print("\n".join(html_links))