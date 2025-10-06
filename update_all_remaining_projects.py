import os
import re

def get_file_content(path):
    try:
        with open(path, 'r', encoding='utf-8') as f:
            return f.read()
    except FileNotFoundError:
        return ""

def write_file_content(path, content):
    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)

def create_document_list(project_name):
    dir_path = f"{project_name}/files/docs"
    if not os.path.exists(dir_path):
        return ""
    files = [f for f in os.listdir(dir_path) if f.endswith('.pdf')]
    files.sort()
    html_links = []
    for filename in files:
        display_name = filename.split('.pdf')[0]
        # remove leading numbers and dot
        display_name = re.sub(r'^\d+\.\s*', '', display_name)
        link = f'<a target="_blank" href="files/docs/{filename}">{display_name}</a><br>'
        html_links.append(link)
    return "\n".join(html_links)

def update_project(project_name):
    print(f"Updating project: {project_name}")
    # Get original index.html content
    index_html_content = get_file_content(f"{project_name}/index.html")
    if not index_html_content:
        print(f"Could not read index.html for {project_name}")
        return

    # Get new content for services section
    services_content_path = f"{project_name}/files/docs/footer/1. Текст.txt"
    if not os.path.exists(services_content_path):
        services_content_path = f"{project_name}/files/docs/footer/1. Текст.docx" # Fallback to docx if txt not found

    services_content = get_file_content(services_content_path)
    if not services_content:
        print(f"Could not read services content for {project_name}")

    services_html = ""
    for line in services_content.splitlines():
        if line.strip():
            services_html += f"<p>{line}</p>\n"

    # Get content for shareholders page
    shareholders_content_path = f"{project_name}/files/docs/footer/3. текст для страницы -информация о структуре и составе акционеров.txt"
    shareholders_content = get_file_content(shareholders_content_path)
    if not shareholders_content:
        print(f"Could not read shareholders content for {project_name}")

    # Get content for executive page
    executive_content_path = f"{project_name}/files/docs/footer/4. текст для страницы - информация о лице, осуществляющем функции единоличного исполнительного органа.txt"
    executive_content = get_file_content(executive_content_path)
    if not executive_content:
        print(f"Could not read executive content for {project_name}")

    # Create shareholders page
    shareholders_page_html = f"""<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Информация о структуре и составе акционеров - {project_name}</title>
  <link href="assets/css/main.css" rel="stylesheet">
</head>
<body>
  <h1>{shareholders_content.splitlines()[0] if shareholders_content else ""}</h1>
  <p>{' '.join(shareholders_content.splitlines()[2:]) if shareholders_content else ""}</p>
</body>
</html>"""
    write_file_content(f"{project_name}/shareholders.html", shareholders_page_html)

    # Create executive page
    executive_page_html = f"""<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Информация о лице, осуществляющем функции единоличного исполнительного органа - {project_name}</title>
  <link href="assets/css/main.css" rel="stylesheet">
</head>
<body>
  <h1>{executive_content.splitlines()[1] if executive_content and len(executive_content.splitlines()) > 1 else ""}</h1>
  <p>{' '.join(executive_content.splitlines()[3:]) if executive_content and len(executive_content.splitlines()) > 3 else ""}</p>
</body>
</html>"""
    write_file_content(f"{project_name}/executive.html", executive_page_html)

    # Update index.html
    document_list_html = create_document_list(project_name)

    # Replace hero-content
    index_html_content = re.sub(r'(<div style="text-align: justify;" class="hero-content"[^>]*>)(.*?)(</div>)', f'\\1\n<p class="mb-4 mb-md-5">{document_list_html}</p>\n\\3', index_html_content, flags=re.DOTALL)

    # Replace services section
    index_html_content = re.sub(r'(<section id="services" class="services section">)(.*?)(</section>)', f'\\1\n<div style="text-align: justify;" class="container section-title" data-aos="fade-up">{services_html}</div>\n\\3', index_html_content, flags=re.DOTALL)

    # Add links
    index_html_content = index_html_content.replace(f'Информация о структуре и составе акционеров ООО МКК «{project_name}»', f'<a href="shareholders.html">Информация о структуре и составе акционеров ООО МКК «{project_name}»</a>')
    index_html_content = index_html_content.replace(f'Информация о лице, осуществляющем функции единоличного исполнительного органа ООО МКК «{project_name}»', f'<a href="executive.html">Информация о лице, осуществляющем функции единоличного исполнительного органа ООО МКК «{project_name}»</a>')
    index_html_content = index_html_content.replace(f'Информация о графике работе ООО МКК «{project_name}» и обособленных подразделений', f'<a href="files/docs/режим работы и обособленные подразделения.pdf" target="_blank">Информация о графике работе ООО МКК «{project_name}» и обособленных подразделений</a>')

    write_file_content(f"{project_name}/index.html", index_html_content)
    print(f"Finished updating project: {project_name}")

projects = ["FastFinance", "FlashZaim", "Frida", "Gulden", "Hyacinth", "QuickMoney"]
for project in projects:
    update_project(project)

print("All projects updated successfully.")