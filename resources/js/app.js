import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import Swal from 'sweetalert2';
import '../css/app.css';

window.Swal = Swal;

document.addEventListener('livewire:init', () => {
    Livewire.on('domain-saved', ({ domain }) => {
        Swal.fire({
            icon: 'success',
            title: 'Tersimpan',
            text: `Domain ${domain} berhasil disimpan.`,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 1800,
            timerProgressBar: true,
        });
    });

    Livewire.on('domain-created', ({ domain }) => {
        Swal.fire({
            icon: 'success',
            title: 'Domain ditambahkan',
            text: `${domain} berhasil disimpan.`,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 1800,
            timerProgressBar: true,
        });
    });

    Livewire.on('domain-email-sent', ({ domain }) => {
        Swal.fire({
            icon: 'success',
            title: 'Email terkirim',
            text: `${domain} berhasil diproses.`,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 1800,
            timerProgressBar: true,
        });
    });
});
