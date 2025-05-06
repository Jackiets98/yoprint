# CSV Upload & Processing App (Laravel)

This Laravel-based application allows users to upload CSV files which are then processed asynchronously in the background. It also displays a real-time history of uploads and their statuses.

## ✅ Features

- Upload CSV files through a simple UI
- Process files in the background using Laravel Queues
- Auto-upsert product data by `UNIQUE_KEY`
- Prevent duplicate uploads via hash check
- Real-time UI updates (polling)
- Clean UTF-8 handling and column validation

## 🛠 Installation

1. Clone the repository:
    ```bash
    git clone https://github.com/Jackiets98/yoprint.git
    cd yoprint
    ```

2. Install dependencies:
    ```bash
    composer install
    npm install && npm run build
    ```

3. Configure environment:
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4. Set up database:
    ```bash
    php artisan migrate
    ```

5. Create storage symlink:
    ```bash
    php artisan storage:link
    ```

6. Start background worker:
    ```bash
    php artisan queue:work
    ```

7. Run the app:
    ```bash
    php artisan serve
    ```

## 🧪 CSV Format

| Column Name           | Required |
|-----------------------|----------|
| UNIQUE_KEY            | ✅       |
| PRODUCT_TITLE         | ✅       |
| PRODUCT_DESCRIPTION   | ✅       |
| STYLE#                | ✅       |
| SANMAR_MAINFRAME_COLOR| ✅       |
| SIZE                  | ✅       |
| COLOR_NAME            | ✅       |
| PIECE_PRICE           | ✅       |

Make sure your file is UTF-8 encoded and headers match exactly.

## 📂 File Upload Path

Uploaded files are stored in `storage/app/public/uploads`.

## 🔧 Background Job

This app uses Laravel Queues. Jobs are dispatched automatically when a file is uploaded.

## 📜 License

MIT
