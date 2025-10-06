import os
import re

def get_file_content(path):
    try:
        with open(path, 'r', encoding='utf-8') as f:
            return f.read()
    except FileNotFoundError:
        print(f"Warning: File not found at {path}")
        return None

def write_file_content(path, content):
    os.makedirs(os.path.dirname(path), exist_ok=True)
    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)

def clean_text(text):
    if not text:
        return "", ""
    lines = [line.strip() for line in text.splitlines() if line.strip()]
    lines = [re.sub(r'^(Текст для отображения:|Заголовок страницы:|Текст страницы:)', '', line).strip() for line in lines]

    # Heuristic to find title: often all caps or the first meaningful line.
    title = ""
    if lines:
        # Check for all-caps line as title
        for i, line in enumerate(lines):
            if line.isupper() and len(line) > 10:
                title = line
                # The rest is content
                content_lines = lines[i+1:]
                return title, "\n".join(content_lines)

        # Fallback to the first line as title
        title = lines[0]
        content_lines = lines[1:]
        return title, "\n".join(content_lines)

    return "", ""


def create_document_list_html(project_name):
    dir_path = f"{project_name}/files/docs"
    if not os.path.exists(dir_path):
        return ""
    files = [f for f in os.listdir(dir_path) if f.endswith('.pdf')]
    try:
        files.sort(key=lambda x: int(re.match(r'(\d+)', x).group(1)) if re.match(r'(\d+)', x) else float('inf'))
    except (ValueError, AttributeError):
        files.sort() # Fallback to alphabetical sort if number parsing fails

    html_links = []
    for filename in files:
        display_name = os.path.splitext(filename)[0]
        display_name = re.sub(r'^\d+\.\s*', '', display_name)
        link = f'<a target="_blank" href="files/docs/{filename}">{display_name}</a><br>'
        html_links.append(link)
    return "\n".join(html_links)

def create_styled_page(project_name, page_type, title, content):
    template_html = get_file_content(f"{project_name}/index.html")
    if not template_html:
        return

    styled_html = re.sub(r'<title>.*?</title>', f'<title>{title} - {project_name}</title>', template_html)

    content_html = ""
    for line in content.splitlines():
        if line.strip():
             content_html += f'<p style="line-height: 1.6; color: #555; margin-bottom: 1rem;">{line}</p>\n'

    new_main_content = f"""
    <main class="main">
        <section id="styled-page" class="section" style="padding: 60px 0;">
            <div class="container">
                <div class="content" style="max-width: 800px; margin: 20px auto; padding: 30px; background-color: #ffffff; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                    <h2 style="color: #6b46c1; margin-bottom: 20px; font-size: 24px; text-align: center; border-bottom: 1px solid #eee; padding-bottom: 15px;">{title}</h2>
                    {content_html}
                </div>
            </div>
        </section>
    </main>
    """

    styled_html = re.sub(r'<main class="main">.*?</main>', new_main_content, styled_html, flags=re.DOTALL)
    write_file_content(f"{project_name}/{page_type}.html", styled_html)
    print(f"Successfully created styled {page_type}.html for {project_name}")

def update_project(project_name):
    print(f"--- Updating {project_name} ---")

    index_path = f"{project_name}/index.html"
    index_html = get_file_content(index_path)
    if not index_html:
        print(f"Error: Could not read index.html for {project_name}. Skipping project.")
        return

    # 1. Update hero-content with document list
    doc_list_html = create_document_list_html(project_name)
    index_html = re.sub(
        r'(<div style="text-align: justify;" class="hero-content".*?>).*?(<\/div>)',
        f'\\1\n<p class="mb-4 mb-md-5">{doc_list_html}</p>\n\\2',
        index_html,
        flags=re.DOTALL
    )

    # 2. Update footer services section
    services_content_raw = get_file_content(f"{project_name}/files/docs/footer/1. Текст.txt")
    _, services_content = clean_text(services_content_raw)
    services_html = "".join([f'<p>{line}</p>' for line in services_content.splitlines() if line.strip()])

    index_html = re.sub(
        r'(<div style="text-align: justify;" class="container section-title" data-aos="fade-up">).*?(<\/div>)',
        f'\\1\n<br>\n{services_html}\n<br>\n\\2',
        index_html,
        flags=re.DOTALL
    )

    # 3. Add links to footer content
    index_html = index_html.replace(f"Информация о структуре и составе акционеров ООО МКК «{project_name}»", f'<a href="shareholders.html" class="highlight">Информация о структуре и составе акционеров ООО МКК «{project_name}»</a>')
    index_html = index_html.replace(f"Информация о лице, осуществляющем функции единоличного исполнительного органа ООО МКК «{project_name}»", f'<a href="executive.html" class="highlight">Информация о лице, осуществляющем функции единоличного исполнительного органа ООО МКК «{project_name}»</a>')
    index_html = index_html.replace(f"Информация о графике работе ООО МКК «{project_name}» и обособленных подразделений", f'<a href="files/docs/режим работы и обособленные подразделения.pdf" target="_blank" class="highlight">Информация о графике работе ООО МКК «{project_name}» и обособленных подразделений</a>')

    write_file_content(index_path, index_html)
    print(f"Updated index.html for {project_name}")

    # 4. Create styled pages
    shareholders_raw = get_file_content(f"{project_name}/files/docs/footer/3. текст для страницы -информация о структуре и составе акционеров.txt")
    if shareholders_raw:
        s_title, s_content = clean_text(shareholders_raw)
        create_styled_page(project_name, "shareholders", s_title, s_content)

    executive_raw = get_file_content(f"{project_name}/files/docs/footer/4. текст для страницы - информация о лице, осуществляющем функции единоличного исполнительного органа.txt")
    if executive_raw:
        e_title, e_content = clean_text(executive_raw)
        create_styled_page(project_name, "executive", e_title, e_content)

    print(f"--- Finished {project_name} ---\n")

if __name__ == "__main__":
    projects = ["FastFinance", "FlashZaim", "Frida", "Gulden", "Hyacinth", "QuickMoney"]
    for p in projects:
        update_project(p)
    print("All projects have been updated successfully.")