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

def clean_text(text):
    text = text.replace("Текст для отображения:", "").strip()
    text = text.replace("Заголовок страницы:", "").strip()
    text = text.replace("Текст страницы:", "").strip()
    lines = [line for line in text.splitlines() if line.strip()]
    return "\n".join(lines)

def create_document_list_html(project_name):
    dir_path = f"{project_name}/files/docs"
    if not os.path.exists(dir_path):
        return ""

    files = [f for f in os.listdir(dir_path) if f.endswith('.pdf')]
    files.sort(key=lambda x: int(re.match(r'(\d+)', x).group(1)) if re.match(r'(\d+)', x) else float('inf'))

    html_links = []
    for filename in files:
        display_name = os.path.splitext(filename)[0]
        display_name = re.sub(r'^\d+\.\s*', '', display_name)
        link = f'<a target="_blank" href="files/docs/{filename}">{display_name}</a><br>'
        html_links.append(link)
    return "\n".join(html_links)

def create_styled_page(project_name, page_type, page_title_for_tag, full_content):
    template_html = get_file_content(f"{project_name}/index.html")
    if not template_html:
        return ""

    styled_html = re.sub(r'<title>.*?</title>', f'<title>{page_title_for_tag} - {project_name}</title>', template_html)

    content_lines = full_content.splitlines()
    content_title = content_lines[0].strip() if content_lines else page_title_for_tag
    page_content_html = "".join([f'<p style="line-height: 1.8; color: #555; margin-bottom: 15px;">{line}</p>' for line in content_lines[1:] if line.strip()])

    new_main_content = f"""
    <main class="main">
        <section id="styled-page" class="section">
            <div class="container">
                <div class="content" style="max-width: 800px; margin: 40px auto; padding: 30px; background-color: #fff; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                    <h2 style="color: #333; margin-bottom: 25px; font-size: 28px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px;">{content_title}</h2>
                    {page_content_html}
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
    original_index_html = get_file_content(index_path)
    if not original_index_html:
        print(f"Error: Could not read index.html for {project_name}. Skipping project.")
        return

    # Keep a copy of the original footer
    original_footer_match = re.search(r'<footer id="footer" class="footer">.*?</footer>', original_index_html, re.DOTALL)
    original_footer = original_footer_match.group(0) if original_footer_match else ""

    doc_list_html = create_document_list_html(project_name)
    index_html = re.sub(
        r'(<div style="text-align: justify;" class="hero-content".*?>).*?(<\/div>)',
        f'\\1\n<p class="mb-4 mb-md-5">{doc_list_html}</p>\n\\2',
        original_index_html,
        flags=re.DOTALL
    )

    services_content = clean_text(get_file_content(f"{project_name}/files/docs/footer/1. Текст.txt"))
    services_html = "".join([f'<p>{line}</p>' for line in services_content.splitlines() if line.strip()])

    # Replace only the inner content of the target div in the footer
    if original_footer:
         updated_footer = re.sub(
            r'(<div style="text-align: justify;" class="container section-title".*?>).*?(<\/div>)',
            f'\\1\n{services_html}\n\\2',
            original_footer,
            flags=re.DOTALL
        )
         index_html = re.sub(r'<footer id="footer" class="footer">.*?</footer>', updated_footer, index_html, flags=re.DOTALL)


    index_html = index_html.replace(f"Информация о структуре и составе акционеров ООО МКК «{project_name}»", f'<a href="shareholders.html">Информация о структуре и составе акционеров ООО МКК «{project_name}»</a>')
    index_html = index_html.replace(f"Информация о лице, осуществляющем функции единоличного исполнительного органа ООО МКК «{project_name}»", f'<a href="executive.html">Информация о лице, осуществляющем функции единоличного исполнительного органа ООО МКК «{project_name}»</a>')
    index_html = index_html.replace(f"Информация о графике работе ООО МКК «{project_name}» и обособленных подразделений", f'<a href="files/docs/режим работы и обособленные подразделения.pdf" target="_blank">Информация о графике работе ООО МКК «{project_name}» и обособленных подразделений</a>')

    write_file_content(index_path, index_html)
    print(f"Updated index.html for {project_name}")

    shareholders_content = clean_text(get_file_content(f"{project_name}/files/docs/footer/3. текст для страницы -информация о структуре и составе акционеров.txt"))
    if shareholders_content:
        create_styled_page(project_name, "shareholders", "Информация о структуре и составе акционеров", shareholders_content)

    executive_content = clean_text(get_file_content(f"{project_name}/files/docs/footer/4. текст для страницы - информация о лице, осуществляющем функции единоличного исполнительного органа.txt"))
    if executive_content:
        create_styled_page(project_name, "executive", "Информация о лице, осуществляющем функции единоличного исполнительного органа", executive_content)

    print(f"--- Finished {project_name} ---\n")

if __name__ == "__main__":
    projects = ["FastFinance", "FlashZaim", "Frida", "Gulden", "Hyacinth", "QuickMoney"]
    for p in projects:
        update_project(p)
    print("All projects have been updated successfully.")