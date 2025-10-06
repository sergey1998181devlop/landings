import os

def get_file_content(path):
    with open(path, 'r', encoding='utf-8') as f:
        return f.read()

def write_file_content(path, content):
    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)

def create_document_list(project_name):
    dir_path = f"{project_name}/files/docs"
    files = [f for f in os.listdir(dir_path) if f.endswith('.pdf')]
    files.sort()
    html_links = []
    for filename in files:
        display_name = filename.split('.pdf')[0]
        if len(display_name) > 2 and display_name[0].isdigit() and (display_name[1] == '.' or display_name[2] == '.'):
            display_name = " ".join(display_name.split(' ')[1:])
        link = f'<a target="_blank" href="/files/docs/{filename}">{display_name}</a><br>'
        html_links.append(link)
    return "\n".join(html_links)

def update_project(project_name):
    # Get original index.html content
    index_html_content = get_file_content(f"{project_name}/index.html")

    # Get new content for services section
    services_content = get_file_content(f"{project_name}/files/docs/footer/1. Текст.txt")
    services_html = ""
    for line in services_content.splitlines():
        if line.strip():
            if "http" in line:
                parts = line.split("http")
                text = parts[0]
                url = "http" + parts[1]
                services_html += f'<p>{text}<a href="{url}" target="_blank">{url}</a></p>\n'
            else:
                services_html += f"<p>{line}</p>\n"

    # Get content for shareholders page
    shareholders_content = get_file_content(f"{project_name}/files/docs/footer/3. текст для страницы -информация о структуре и составе акционеров.txt")

    # Get content for executive page
    executive_content = get_file_content(f"{project_name}/files/docs/footer/4. текст для страницы - информация о лице, осуществляющем функции единоличного исполнительного органа.txt")

    # Create shareholders page
    shareholders_page_html = f"""<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Информация о структуре и составе акционеров - {project_name}</title>
  <meta name="description" content="">
  <meta name="keywords" content="">
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
</head>
<body class="index-page">
  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="header-container container-fluid container-xl position-relative d-flex align-items-center justify-content-between">
      <a href="index.html" class="logo d-flex align-items-center me-auto me-xl-0">
        <h1 class="sitename">{project_name}</h1>
      </a>
      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.html#hero" class="active">Главная</a></li>
          <li><a href="index.html#about">О Сервисе</a></li>
          <li><a href="index.html#docs">Документы</a></li>
          <li><a href="index.html#how">Как получить займ</a></li>
        </ul>
      </nav>
    </div>
  </header>
  <main class="main">
    <section id="about" class="about section">
      <div class="container" data-aos="fade-up" data-aos-delay="100">
        <div class="row align-items-xl-center gy-5">
          <div class="col-xl-12">
            <div class="row gy-4 icon-boxes">
                <div class="col-md-12" data-aos="fade-up" data-aos-delay="200">
                    <div class="icon-box">
                        <h2 class="title">{shareholders_content.splitlines()[0]}</h2>
                        <p class="description">{' '.join(shareholders_content.splitlines()[2:])}</p>
                    </div>
                </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
  <footer id="footer" class="footer">
    <div class="container copyright text-center mt-4">
        <p>©<strong class="px-1 sitename">{project_name}</strong> <span>Все права защищены</span></p>
    </div>
  </footer>
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/js/main.js"></script>
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
  <meta name="description" content="">
  <meta name="keywords" content="">
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
</head>
<body class="index-page">
  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="header-container container-fluid container-xl position-relative d-flex align-items-center justify-content-between">
      <a href="index.html" class="logo d-flex align-items-center me-auto me-xl-0">
        <h1 class="sitename">{project_name}</h1>
      </a>
      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.html#hero" class="active">Главная</a></li>
          <li><a href="index.html#about">О Сервисе</a></li>
          <li><a href="index.html#docs">Документы</a></li>
          <li><a href="index.html#how">Как получить займ</a></li>
        </ul>
      </nav>
    </div>
  </header>
  <main class="main">
    <section id="about" class="about section">
      <div class="container" data-aos="fade-up" data-aos-delay="100">
        <div class="row align-items-xl-center gy-5">
          <div class="col-xl-12">
            <div class="row gy-4 icon-boxes">
                <div class="col-md-12" data-aos="fade-up" data-aos-delay="200">
                    <div class="icon-box">
                        <h2 class="title">{executive_content.splitlines()[1]}</h2>
                        <p class="description">{' '.join(executive_content.splitlines()[3:])}</p>
                    </div>
                </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
  <footer id="footer" class="footer">
    <div class="container copyright text-center mt-4">
        <p>©<strong class="px-1 sitename">{project_name}</strong> <span>Все права защищены</span></p>
    </div>
  </footer>
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/js/main.js"></script>
</body>
</html>"""
    write_file_content(f"{project_name}/executive.html", executive_page_html)

    # Update index.html
    document_list_html = create_document_list(project_name)

    start_hero_content = index_html_content.find('<div style="text-align: justify;" class="hero-content" data-aos="fade-up" data-aos-delay="200">')
    end_hero_content = index_html_content.find('</p>', start_hero_content)

    updated_index_html = index_html_content[:start_hero_content] + '<div style="text-align: justify;" class="hero-content" data-aos="fade-up" data-aos-delay="200">\n' + f'<p class="mb-4 mb-md-5">{document_list_html}</p>' + index_html_content[end_hero_content:]

    start_services = updated_index_html.find('<section id="services" class="services section">')
    end_services = updated_index_html.find('</section><!-- /Services Section -->', start_services)

    final_index_html = updated_index_html[:start_services] + f'<section id="services" class="services section">\n<div style="text-align: justify;" class="container section-title" data-aos="fade-up">\n{services_html}</div>\n' + updated_index_html[end_services:]

    final_index_html = final_index_html.replace(f'Информация о структуре и составе акционеров ООО МКК «{project_name}»', f'<a href="shareholders.html">Информация о структуре и составе акционеров ООО МКК «{project_name}»</a>')
    final_index_html = final_index_html.replace(f'Информация о лице, осуществляющем функции единоличного исполнительного органа ООО МКК «{project_name}»', f'<a href="executive.html">Информация о лице, осуществляющем функции единоличного исполнительного органа ООО МКК «{project_name}»</a>')
    final_index_html = final_index_html.replace(f'Информация о графике работе ООО МКК «{project_name}» и обособленных подразделений', f'<a href="files/docs/режим работы и обособленные подразделения.pdf" target="_blank">Информация о графике работе ООО МКК «{project_name}» и обособленных подразделений</a>')

    write_file_content(f"{project_name}/index.html", final_index_html)


projects = ["Frida", "Gulden", "Hyacinth", "QuickMoney"]
for project in projects:
    update_project(project)

# Update FastFinance and FlashZaim as well
update_project("FastFinance")
update_project("FlashZaim")

print("All projects updated successfully.")