#  -*- coding: utf-8 -*-
import requests
from bs4 import BeautifulSoup
from datetime import datetime

url = "https://cuescore.com/centre-valdeloire/tournaments?q=&d=0&season=0&s=0"

response = requests.get(url)
soup = BeautifulSoup(response.text, "html.parser")

# Sélection de toutes les balises tr avec la class tournament
tournois = soup.find_all("tr", class_="tournament")

# Ouvrir un fichier en mode écriture
with open("tournois.txt", "r", encoding="utf-8") as fichier:
    lignes_existantes = fichier.readlines()

# Créer une liste pour stocker les nouvelles informations
nouvelles_informations = []


# Parcourir les tournois et extraire les informations
for tournoi in tournois:
    # Le nom du tournoi se trouve dans la balise a avec la class bold
    nom_tournoi = tournoi.find("a", class_="bold").text 

    # L'URL du tournoi se trouve dans l'attribut "href" de la balise a
    url_tournoi = tournoi.find("a", class_="bold")["href"]  

    # Vérifier si les informations sont nouvelles
    if(f"Nom du tournoi: {nom_tournoi}\n" not in lignes_existantes and
       f"URL du tournoi: {url_tournoi}\n" not in lignes_existantes):
        nouvelles_informations.append(f"Nom du tournoi: {nom_tournoi}\n")
        nouvelles_informations.append(f"URL du tournoi: {url_tournoi}\n")
        nouvelles_informations.append("--------------------------------\n")

# Si de nouvelles informations ont été trouvées, les ecrire dans le fichier tournois.txt
if nouvelles_informations:
    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    with open("tournois.txt", "w", encoding="utf-8") as fichier:
        nouvelles_informations.append(f"Timestamp : {timestamp}\n")
        fichier.writelines(nouvelles_informations)
        print("Les nouvelles données ont été ajoutées dans le fichier")
    
else:
    print("Aucune nouvelle information n'a été trouvée")
