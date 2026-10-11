datastream = 'DCDEAABEB' #mendefinisikan nilai/karakter pada string "datastream"
symbol = 'ABCDE' #mendefinisikan nilai/karakter symbol dengan isi 'ABCDE'
i = 0 #mendefinisikan nilai indeks i = 0
print(datastream) #mencetak datastream

for x in range(len(datastream)): #proses looping untuk melakukan pengulangan dengan rentang panjang dari variabel 'datastream'
  for j in range(len(symbol)): #proses looping kedua untuk melakukan pengulangan dengan rentang panjang dari variabel 'symbol'
    if datastream[x] == symbol[i]: #kondisi if untuk memeriksa apakah karakter ke-x dalam 'datastream' sama dengan karakter ke-i dalam 'symbol'
      print(i, end = '') #jika kondisi terpenuhi, maka i akan dicetak dan fungsi end = '' akan membuat output tercetak dalam satu baris.
      i = i+1 #menambahkan nilai i dengan 1 untuk mengecek datastream berikutnya pada pengulangan yang baru
    else: #jika kondisi if tidak terpenuhi, maka masuk ke proses else
      i = i+1 #menambahkan nilai i dengan 1 meskipun kondisi if tidak terpenuhi
  i = 0 #setelah looping kedua selesai, nilai i akan kembali menjadi 0 untuk pengecekan dari awal untuk simbol berikutnya.
