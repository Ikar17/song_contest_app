document.addEventListener('DOMContentLoaded', () =>{
    singerElement = document.getElementById('searchSongBySinger');
    titleElement = document.getElementById('searchSongByTitle');
    form = document.getElementById('searchSongForm');
    resultsContainer = document.getElementById('searchSongsResults');

    if(form){
        form.addEventListener('input', async () => {
            const songs = await getSongs(singerElement.value, titleElement.value);
            console.log(songs);
            showSongs(songs, resultsContainer);
        })
    }
})

function getSongs(singer, title) {
    if(singer == undefined || title == undefined || (singer.length < 1 && title.length < 1 )) return {results : []};

    data = {
        singer : singer,
        title : title
    }

    return fetch('../php_scripts/search_songs.php', {
        method: 'POST',
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(data)
      })
      .then(response => response.json())
      .then(data => {
        return data;
      });
    
}

function showSongs(data, containerHandler){
    if(data == undefined || containerHandler == undefined){
        console.log("Brak danych");
        return;
    }

    containerHandler.innerHTML = "";
    
    if(data.results.length == 0){
        const songDiv = document.createElement('div');
        songDiv.innerText = `Brak rezultatów`;
        containerHandler.appendChild(songDiv);
        return;
    }

    for(let i=0; i < data.results.length; i++){
        //pobieranie danych
        const singer = data.results[i].Wykonawca;
        const title = data.results[i].Tytul;
        //tworzenie elementu html
        const songDiv = document.createElement('div');
        songDiv.classList.add('song');
        songDiv.innerText = `${i+1}.  ${singer} - ${title}`;
        containerHandler.appendChild(songDiv);
    }
}

function showSidebar(){
    const sidebar = document.getElementById('mobile-sidebar');
    if(sidebar){
        sidebar.style.display = 'flex';
    }
}

function hideSidebar(){
    const sidebar = document.getElementById('mobile-sidebar');
    if(sidebar){
        sidebar.style.display = 'none';
    }
}


async function delete_song(id_song){
    if(id_song == undefined) return;
    id_song = parseInt(id_song);
    if(typeof id_song != 'number') return;

    data = {
        id : id_song
    };

    json = JSON.stringify(data)

    await fetch('../php_scripts/delete_participant_admin.php', {
        method: 'POST',
        headers: {
            "Content-Type": "application/json"
        },
        body: json
      })
      .then(response => response.json())
      .then(data => {
        return data;
      });
 
    window.location.pathname = "./songcontest/pages/admin_panel.php";     
}
