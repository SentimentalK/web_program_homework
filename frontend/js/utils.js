async function loadCourses() {
    try {
        const headers = {};
        const token = localStorage.getItem('token');
        if (token) headers['Authorization'] = `Bearer ${token}`;

        const response = await fetch('/api/courses/list', { headers });
        
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

        const data = await response.json();
        if (!data.course_ids?.length) throw new Error('No courses available');

        return data.course_ids;
    } catch (error) {
        showError(error.message);
    }
}

function playChapter(chapter) {
    const btn = document.getElementById(`play-${chapter}`);
    const audioFile = `./src/${chapter}.mp3`;
    
    
    if(!window.currentAudio) {
        window.currentAudio = new Audio(audioFile);
        window.currentAudio.onerror = () => {
            alert(`Audio file ${audioFile} not found`);
            btn.classList.remove('playing');
        };
    }

    if(window.currentAudio.paused) {
        window.currentAudio.src = audioFile;
        window.currentAudio.play()
            .catch(error => {
                alert(`Error playing audio: ${error.message}`);
            });
        btn.classList.add('playing');
    } else {
        window.currentAudio.pause();
        btn.classList.remove('playing');
    }
}