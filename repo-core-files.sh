# First create the basic structure:
mkdir -p traderate-pro
cd traderate-pro
git init

# Create directory structure
mkdir -p {app/{Controllers,Models,Services,Middleware,Helpers},config,public/{assets/{css,js,images},uploads},resources/views,storage/{logs,cache,uploads},database,tests}

# Create initial files
touch README.md
touch .gitignore
touch composer.json
touch .env.example
