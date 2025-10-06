import os
import re

def get_file_content(path):
    try:
        with open(path, 'r', encoding='utf-8') as f:
            return f.read()
    except FileNotFoundError:
        print(f"Warning: File not found at {path}")
        return ""

def write_file_content(path, content):
    os.makedirs(os.path.dirname(path), exist_ok=True)
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
        display_name = os.path.splitext(filename)[0]
        display_name = re.sub(r'^\d+\.\s*', '', display_name)
        link = f'<a target="_blank" href="files/docs/{filename}">{display_name}</a><br>'
        html_links.append(link)
    return "\n".join(html_links)

def create_styled_page(template_content, project_name, page_title, content_title, page_content):
    page_html = template_content
    # Update title
    page_html = re.sub(r'<title>.*?</title>', f'<title>{page_title} - {project_name}</title>', page_html)

    # Create new body content
    new_body = f"""
  <main class="main">
    <section class="section">
      <div class="container">
        <div class="content" style="max-width: 800px; margin: 20px auto; padding: 20px; background-color: #fff; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h2 style="color: #6b46c1; margin-bottom: 20px; font-size: 24px;">{content_title}</h2>
            <p style="line-height: 1.6; color: #555;">{page_content}</p>
        </div>
      </div>
    </section>
  </main>
    """

    # Replace main content
    page_html = re.sub(r'<main class="main">.*?</main>', new_body, page_html, flags=re.DOTALL)
    return page_html

def update_project(project_name):
    print(f"--- Starting update for project: {project_name} ---")

    # 1. Read all necessary files
    index_path = f"{project_name}/index.html"
    index_html_content = get_file_content(index_path)
    if not index_html_content:
        print(f"Error: Could not read index.html for {project_name}. Skipping project.")
        return

    services_content = get_file_content(f"{project_name}/files/docs/footer/1. Текст.txt")
    shareholders_content = get_file_content(f"{project_name}/files/docs/footer/3. текст для страницы -информация о структуре и составе акционеров.txt")
    executive_content = get_file_content(f"{project_name}/files/docs/footer/4. текст для страницы - информация о лице, осуществляющем функции единоличного исполнительного органа.txt")

    # 2. Create new styled pages
    if shareholders_content:
        shareholders_page = create_styled_page(index_html_content, project_name, "Информация о структуре и составе акционеров", "ИНФОРМАЦИЯ О СТРУКТУРЕ И СОСТАВЕ АКЦИОНЕРОВ", shareholders_content)
        write_file_content(f"{project_name}/shareholders.html", shareholders_page)
        print(f"Created shareholders.html for {project_name}")

    if executive_content:
        executive_page = create_styled_page(index_html_content, project_name, "Информация о лице, осуществляющем функции единоличного исполнительного органа", "ИНФОРМАЦИЯ О ЛИЦЕ, ОСУЩЕСТВЛЯЮЩЕМ ФУНКЦИИ ЕДИНОЛИЧНОГО ИСПОЛНИТЕЛЬНОГО ОРГАНА", executive_content)
        write_file_content(f"{project_name}/executive.html", executive_page)
        print(f"Created executive.html for {project_name}")

    # 3. Update index.html
    # Update hero-content with document list
    doc_list_html = create_document_list(project_name)
    index_html_content = re.sub(r'<div style="text-align: justify;" class="hero-content".*?>.*?<\/p>', f'<div style="text-align: justify;" class="hero-content" data-aos="fade-up" data-aos-delay="200">\n<p class="mb-4 mb-md-5">{doc_list_html}</p>', index_html_content, flags=re.DOTALL)

    # Update services section in footer
    services_html = "".join([f"<p>{line}</p>\n" for line in services_content.splitlines() if line.strip()])
    index_html_content = re.sub(r'<section id="services".*?>.*?<\/section>', f'<section id="services" class="services section">\n<div style="text-align: justify;" class="container section-title" data-aos="fade-up">\n{services_html}\n</div>\n</section>', index_html_content, flags=re.DOTALL)

    # Add links in the updated services/footer section
    index_html_content = index_html_content.replace(f"Информация о структуре и составе акционеров ООО МКК «{project_name}»", f'<a href="shareholders.html">Информация о структуре и составе акционеров ООО МКК «{project_name}»</a>')
    index_html_content = index_html_content.replace(f"Информация о лице, осуществляющем функции единоличного исполнительного органа ООО МКК «{project_name}»", f'<a href="executive.html">Информация о лице, осуществляющем функции единоличного исполнительного органа ООО МКК «{project_name}»</a>')
    index_html_content = index_html_content.replace(f"Информация о графике работе ООО МКК «{project_name}» и обособленных подразделений", f'<a href="files/docs/режим работы и обособленные подразделения.pdf" target="_blank">Информация о графике работе ООО МКК «{project_name}» и обособленных подразделений</a>')

    write_file_content(index_path, index_html_content)
    print(f"Updated index.html for {project_name}")
    print(f"--- Finished update for project: {project_name} ---\n")

# List of all projects to update
projects_to_update = ["FastFinance", "FlashZaim", "Frida", "Gulden", "Hyacinth", "QuickMoney"]
for project in projects_to_update:
    update_project(project)

print("All projects have been updated.")