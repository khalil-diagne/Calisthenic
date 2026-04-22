from search_file import search_file

# Chercher un fichier précis
chemins = search_file("rapport.pdf", "/home/user/documents")

# Chercher avec wildcard
chemins = search_file("*.txt", "/home/user/documents")

# Sans sous-dossiers
chemins = search_file("config.json", "/etc", recursive=False)

# Traiter les résultats
if chemins:
    for chemin in chemins:
        print(chemin)
else:
    print("Aucun fichier trouvé.")