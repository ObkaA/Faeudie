import requests
import csv
import time

BASE_URL = "https://world.openfoodfacts.org/cgi/search.pl"
PAGE_SIZE = 100
TOTAL_PAGES = 5
OUTPUT_FILE = "produkt_bazowe.csv"

# Rozszerzone kategorie, aby pokryć podstawowe rodzaje produktów spożywczych
CATEGORIES = [
    'fruits', 'vegetables', 'meats', 'fresh-foods', 'grains',
    'pasta', 'dairy-products', 'bread', 'sweets', 'beverages',
    'cereals', 'oils', 'sauces', 'fish', 'eggs' # Dodano więcej kategorii
]

def get_products_from_api(page, page_size, category=None):
    """
    Pobiera produkty z API Open Food Facts dla danej strony i kategorii.
    """
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

def extract_product_info(product, category):
    """
    Wyodrębnia kluczowe informacje o produkcie, włączając kategorię.
    Zaokrągla wartości odżywcze do 1 miejsca po przecinku.
    Ignoruje produkty, których nazwa ma więcej niż 64 znaków.
    """
    name = product.get('product_name', '').strip()

    # Sprawdzamy długość nazwy produktu
    if len(name) > 64:
        print(f"Ignorowanie produktu '{name}' - nazwa ma więcej niż 64 znaków.")
        return None # Zwracamy None, aby zasygnalizować pominięcie produktu

    nutriments = product.get('nutriments', {})

    # Pobieramy wartości i zaokrąglamy je do 1 miejsca po przecinku, jeśli istnieją
    protein = round(nutriments.get('proteins_100g'), 1) if nutriments.get('proteins_100g') is not None else None
    carbs = round(nutriments.get('carbohydrates_100g'), 1) if nutriments.get('carbohydrates_100g') is not None else None
    fat = round(nutriments.get('fat_100g'), 1) if nutriments.get('fat_100g') is not None else None

    # Dodajemy kategorię do zwracanej listy
    return [name, category, protein, carbs, fat]

def save_to_csv(products_with_category, filename):
    """
    Zapisuje listę produktów wraz z ich kategoriami do pliku CSV.
    """
    with open(filename, mode='w', newline='', encoding='utf-8') as f:
        writer = csv.writer(f)
        # Zaktualizowany nagłówek CSV
        writer.writerow(['Nazwa produktu', 'Kategoria', 'Białko (g)', 'Węglowodany (g)', 'Tłuszcz (g)'])

        for product_data in products_with_category:
            # product_data to już gotowa lista [name, category, protein, carbs, fat]
            # Sprawdzamy, czy product_data nie jest None (czyli produkt nie został zignorowany)
            # oraz czy wszystkie podstawowe dane (nazwa, kategoria, białko, węglowodany, tłuszcz) są dostępne
            if product_data and all(product_data):
                writer.writerow(product_data)

def main():
    """
    Główna funkcja programu, orkiestrująca pobieranie i zapisywanie danych.
    """
    all_products_with_category_info = []
    # Zbiór do przechowywania nazw produktów, które już zostały dodane, aby uniknąć duplikatów
    seen_product_names = set()

    print("Pobieranie danych...")

    for category in CATEGORIES:
        print(f"Pobieram kategorię: {category}")
        for page in range(1, TOTAL_PAGES + 1):
            print(f"Strona {page} w kategorii {category}")
            products = get_products_from_api(page=page, page_size=PAGE_SIZE, category=category)
            print(f"Pobrano {len(products)} produktów ze strony {page} w kategorii {category}")

            # Jeśli strona jest pusta, przerywamy pobieranie kolejnych stron dla tej kategorii
            if not products:
                print(f"Pusta strona {page} w kategorii {category}. Przerywam pobieranie dla tej kategorii.")
                break

            for i, product in enumerate(products, start=1):
                # Wywołujemy extract_product_info z przekazaną kategorią
                extracted_info = extract_product_info(product, category)

                # Dodajemy produkt tylko jeśli nie został zignorowany (extracted_info is not None)
                # i jego nazwa nie jest już w zbiorze seen_product_names
                if extracted_info:
                    product_name = extracted_info[0] # Nazwa produktu to pierwszy element w extracted_info
                    if product_name not in seen_product_names:
                        all_products_with_category_info.append(extracted_info)
                        seen_product_names.add(product_name) # Dodajemy nazwę do zbioru
                    else:
                        print(f"Pominięto duplikat produktu: '{product_name}'")

            time.sleep(1) # Opóźnienie, aby nie przeciążać API

    print(f"Łącznie pobrano {len(all_products_with_category_info)} unikalnych rekordów z informacjami o produktach.")
    print("Zapisywanie do CSV...")
    save_to_csv(all_products_with_category_info, OUTPUT_FILE)
    print(f"Zapisano dane do pliku: {OUTPUT_FILE}")

if __name__ == "__main__":
    main()
