import csv

INPUT_FILE = 'produkty_all.csv'
OUTPUT_FILE = 'produkty_all_no_dupe.csv'

def remove_duplicates(input_file, output_file):
    seen = set()
    unique_rows = []

    with open(input_file, mode='r', encoding='utf-8') as f:
        reader = csv.reader(f)
        header = next(reader)
        unique_rows.append(header)

        for row in reader:
            row_tuple = tuple(row)
            if row_tuple not in seen:
                seen.add(row_tuple)
                unique_rows.append(row)

    with open(output_file, mode='w', newline='', encoding='utf-8') as f:
        writer = csv.writer(f)
        writer.writerows(unique_rows)

    print(f"Usunięto duplikaty. Zapisano {len(unique_rows)-1} unikalnych wierszy do {output_file}")

if __name__ == "__main__":
    remove_duplicates(INPUT_FILE, OUTPUT_FILE)