import requests
import csv
import time

BASE_URL = "https://world.openfoodfacts.org/cgi/search.pl"
PAGE_SIZE = 100
TOTAL_PAGES = 5
OUTPUT_FILE = "produkt_bazowe.csv"

# Kategorie które chcemy pobrać (mogą być rozszerzone)
CATEGORIES = ['fruits', 'vegetables', 'meats', 'fresh-foods', 'grains']

def get_products_from_api(page, page_size, category=None):
    params = {
        'action': 'process',
        'page_size': page_size,
        'page': page,
        'json': True,
        'tagtype_1': 'countries',
        'tag_contains_1': 'contains',
        'tag_1': 'poland'
    }
    if category:
        params['tagtype_0'] = 'categories'
        params['tag_contains_0'] = 'contains'
        params['tag_0'] = category

    response = requests.get(BASE_URL, params=params)
    
    if response.status_code != 200:
        print(f"Błąd HTTP: {response.status_code} na stronie {page} dla kategorii {category}")
        return []

    try:
        return response.json().get('products', [])
    except Exception as e:
        print(f"Błąd dekodowania JSON na stronie {page} dla kategorii {category}: {e}")
        return []

def extract_product_info(product):
    name = product.get('product_name', '').strip()
    nutriments = product.get('nutriments', {})

    protein = nutriments.get('proteins_100g')
    carbs = nutriments.get('carbohydrates_100g')
    fat = nutriments.get('fat_100g')

    return [name, protein, carbs, fat]

def save_to_csv(products, filename):
    with open(filename, mode='w', newline='', encoding='utf-8') as f:
        writer = csv.writer(f)
        writer.writerow(['Nazwa produktu', 'Białko (g)', 'Węglowodany (g)', 'Tłuszcz (g)'])

        for product in products:
            row = extract_product_info(product)
            if all(row):
                writer.writerow(row)

def main():
    all_products = []
    print("Pobieranie danych...")

    for category in CATEGORIES:
        print(f"Pobieram kategorię: {category}")
        for page in range(1, TOTAL_PAGES + 1):
            print(f"Strona {page} w kategorii {category}")
            products = get_products_from_api(page=page, page_size=PAGE_SIZE, category=category)
            print(f"Pobrano {len(products)} produktów ze strony {page} w kategorii {category}")

            for i, product in enumerate(products, start=1):
                name = product.get('product_name', 'Brak nazwy').strip()
                print(f"Produkt {i} na stronie {page}, kategoria {category}: {name}")

            all_products.extend(products)
            time.sleep(1)

    print(f"Łącznie pobrano {len(all_products)} produktów.")
    print("Zapisywanie do CSV...")
    save_to_csv(all_products, OUTPUT_FILE)
    print(f"Zapisano dane do pliku: {OUTPUT_FILE}")

if __name__ == "__main__":
    main()
