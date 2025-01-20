# get_last_line.py

with open("tournois.txt", "r", encoding="utf-8") as fichier:
    lines = fichier.readlines()
    last_line = lines[-1].strip()  # Obtenir la dernière ligne et supprimer les espaces vides

print(last_line)