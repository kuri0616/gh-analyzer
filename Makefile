.PHONY: help build run test clean docker-build docker-run

# Default target
help:
	@echo "GitHub Analyzer - Development Commands"
	@echo ""
	@echo "Available commands:"
	@echo "  help         Show this help message"
	@echo "  build        Install dependencies"
	@echo "  run          Run the application with help"
	@echo "  test         Run tests (if available)"
	@echo "  clean        Clean build artifacts"
	@echo "  docker-build Build Docker image"
	@echo "  docker-run   Run using Docker Compose"
	@echo ""
	@echo "Examples:"
	@echo "  make run ARGS='repo octocat/Hello-World'"
	@echo "  make docker-run ARGS='user octocat'"

# Install dependencies
build:
	@if [ -f composer.json ]; then composer install; fi
	@chmod +x gh-analyzer
	@echo "Build complete!"

# Run the application
run:
	@./gh-analyzer $(ARGS)

# Run tests (placeholder for future testing)
test:
	@echo "No tests configured yet"

# Clean build artifacts
clean:
	@rm -rf vendor/
	@rm -f composer.lock
	@echo "Cleaned build artifacts"

# Docker build
docker-build:
	@docker build -t gh-analyzer .
	@echo "Docker image built successfully"

# Docker run with compose
docker-run:
	@docker-compose run --rm gh-analyzer $(ARGS)

# Development environment
dev:
	@docker-compose run --rm gh-analyzer-dev

# Install example
install: build
	@echo "Setting up GitHub Analyzer..."
	@if [ ! -f .env ]; then cp .env.example .env; echo "Created .env file - please edit it to add your GitHub token"; fi
	@echo "Installation complete!"
	@echo ""
	@echo "Next steps:"
	@echo "1. Edit .env file to add your GitHub token"
	@echo "2. Run: make run ARGS='--help'"